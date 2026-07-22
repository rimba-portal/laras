<?php

declare(strict_types=1);

namespace Rimba\Sync\Actions;

use Illuminate\Support\Facades\DB;
use Rimba\Sync\Contracts\DataFetcher;

class FetchDatabaseData implements DataFetcher
{
    public function fetch(array $config): array
    {
        return DB::connection($config['connection'])
            ->select($config['query'], $config['bindings'] ?? []);
    }
}
