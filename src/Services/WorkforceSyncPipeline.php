<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Rimba\Sync\Enums\WorkforceSyncRunStatus;
use Rimba\Sync\Models\WorkforceChange;
use Rimba\Sync\Models\WorkforceSyncRun;
use Rimba\Sync\Support\WorkforceSyncResult;
use Rimba\Wfm\Actions\ApplyWorkforceTransition;
use Rimba\Wfm\Actions\SeparateWorkforce;
use Rimba\Wfm\Enums\WorkforceEventType;

final readonly class WorkforceSyncPipeline
{
    public function __construct(private HrdbWorkforceNormalizer $hrdbWorkforceNormalizer, private WorkforceSnapshotService $workforceSnapshotService, private WorkforceChangeDetectionService $workforceChangeDetectionService, private WorkforceEventClassificationService $workforceEventClassificationService, private WorkforceMasterDataService $workforceMasterDataService, private StaffSynchronizationService $staffSynchronizationService, private ApplyWorkforceTransition $applyWorkforceTransition, private SeparateWorkforce $separateWorkforce) {}

    public function start(?int $apiConfigId = null): WorkforceSyncRun
    {
        return WorkforceSyncRun::create(['uuid' => (string) Str::uuid(), 'api_config_id' => $apiConfigId, 'source' => config('workforce-sync.source', 'hrdb'), 'status' => WorkforceSyncRunStatus::Running, 'started_at' => now()]);
    }

    public function process(WorkforceSyncRun $run, array $raw): WorkforceSyncResult
    {
        try {
            return DB::transaction(function () use ($run, $raw): WorkforceSyncResult {
                $d = $this->hrdbWorkforceNormalizer->normalize($raw);
                $uuid = $d['source_uuid'];
                $hash = $this->workforceSnapshotService->checksum($d);
                $prev = $this->workforceSnapshotService->previous($run->source, $uuid);
                if ($prev && $prev->checksum === $hash && config('workforce-sync.skip_unchanged', true)) {
                    return new WorkforceSyncResult($uuid, 'unchanged');
                }

                $changes = $this->workforceChangeDetectionService->detect($prev?->payload, $d);
                $event = $this->workforceEventClassificationService->classify($prev?->payload, $d, $changes);
                $ids = $this->workforceMasterDataService->resolve($d);
                [$staff,$result] = $this->staffSynchronizationService->sync($d, $ids['org_corp_id'], $ids['org_unit_id']);
                $assignment = array_filter(array_merge($ids, ['effective_from' => $d['work_start_date'] ?? now()->toDateString(), 'source' => $run->source, 'source_reference' => 'hrdb:'.$uuid]), fn ($v): bool => $v !== null);
                $ref = 'hrdb:'.$uuid.':'.$hash;
                $context = ['source' => $run->source, 'source_reference' => $ref, 'effective_at' => $d['source_modified_at'] ? now()->parse($d['source_modified_at']) : now(), 'attributes' => ['sync_run_id' => $run->id]];
                if ($event === WorkforceEventType::Separated) {
                    try {
                        $this->separateWorkforce->execute($staff->id, $context);
                    } catch (\Throwable) {
                    }
                } elseif ($event) {
                    $this->applyWorkforceTransition->execute($staff->id, $event, $assignment, $context);
                }

                $snapshot = $this->workforceSnapshotService->capture($run, $d, $hash);
                foreach ($changes as $change) {
                    WorkforceChange::create(['sync_run_id' => $run->id, 'snapshot_id' => $snapshot->id, 'source_uuid' => $uuid, 'field' => $change['field'], 'before_value' => $change['before'], 'after_value' => $change['after'], 'classification' => $event?->value, 'applied' => true, 'detected_at' => now()]);
                }

                return new WorkforceSyncResult($uuid, $result, $changes, $event?->value);
            });
        } catch (\Throwable $throwable) {
            return new WorkforceSyncResult((string) ($raw['uuid'] ?? 'unknown'), 'failed', [], null, $throwable->getMessage());
        }
    }

    public function finish(WorkforceSyncRun $run, array $counts): WorkforceSyncRun
    {
        $status = ($counts['failed'] ?? 0) > 0 ? WorkforceSyncRunStatus::CompletedWithErrors : WorkforceSyncRunStatus::Completed;
        $run->update(array_merge($counts, ['status' => $status, 'completed_at' => now(), 'summary' => $counts]));

        return $run->refresh();
    }
}
