<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

final class WorkforceChangeDetectionService
{
    public function detect(?array $before, array $after): array
    {
        if ($before === null) {
            return [['field' => '__record__', 'before' => null, 'after' => $after]];
        } $out = [];
        foreach (config('workforce-sync.tracked_fields', []) as $f) {
            $a = data_get($before, $f);
            $b = data_get($after, $f);
            if ($this->norm($a) !== $this->norm($b)) {
                $out[] = ['field' => $f, 'before' => $a, 'after' => $b];
            }
        }

        return $out;
    }

    private function norm(mixed $v): mixed
    {
        return is_string($v) ? trim($v) : $v;
    }
}
