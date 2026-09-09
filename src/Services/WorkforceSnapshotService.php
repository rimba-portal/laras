<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Rimba\Sync\Models\WorkforceSnapshot;
use Rimba\Sync\Models\WorkforceSyncRun;

final class WorkforceSnapshotService
{
    public function canonical(array $data): array
    {
        unset($data['source_modified_at']);

        return $this->sort($data);
    }

    public function checksum(array $data): string
    {
        return hash('sha256', json_encode($this->canonical($data), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
    }

    public function previous(string $source, string $uuid): ?WorkforceSnapshot
    {
        return WorkforceSnapshot::query()->where('source', $source)->where('source_uuid', $uuid)->latest('captured_at')->latest('id')->first();
    }

    public function capture(WorkforceSyncRun $run, array $data, string $checksum): WorkforceSnapshot
    {
        return WorkforceSnapshot::create(['sync_run_id' => $run->id, 'source' => $run->source, 'source_uuid' => $data['source_uuid'], 'checksum' => $checksum, 'payload' => config('workforce-sync.store_normalized_payload', true) ? $data : null, 'source_modified_at' => $data['source_modified_at'] ?? null, 'captured_at' => now()]);
    }

    private function sort(array $a): array
    {
        ksort($a);
        foreach ($a as &$v) {
            if (is_array($v)) {
                $v = $this->sort($v);
            }
        }

        return $a;
    }
}
