<?php

declare(strict_types=1);

namespace Rimba\Sync\Actions;

use Rimba\Sync\Models\ApiData;
use Rimba\Sync\Models\WorkforceSyncRun;
use Rimba\Sync\Services\WorkforceSyncPipeline;

final readonly class SyncWorkforceApiConfig
{
    public function __construct(private WorkforceSyncPipeline $workforceSyncPipeline) {}

    public function execute(int $apiConfigId): WorkforceSyncRun
    {
        $run = $this->workforceSyncPipeline->start($apiConfigId);
        $counts = ['received' => 0, 'processed' => 0, 'created' => 0, 'updated' => 0, 'unchanged' => 0, 'failed' => 0];
        ApiData::query()->where('api_config_id', $apiConfigId)->whereNull('error')->orderBy('id')->chunkById((int) config('workforce-sync.chunk_size', 500), function ($rows) use ($run, &$counts): void {
            foreach ($rows as $row) {
                $counts['received']++;
                $result = $this->workforceSyncPipeline->process($run, (array) $row->payload);
                $counts['processed']++;
                $counts[$result->result] = ($counts[$result->result] ?? 0) + 1;
            }
        });

        return $this->workforceSyncPipeline->finish($run, $counts);
    }
}
