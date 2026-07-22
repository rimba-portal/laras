<?php

declare(strict_types=1);

namespace Rimba\Sync\Actions;

use Illuminate\Support\Facades\Http;
use Rimba\Sync\Contracts\DataFetcher;

class FetchRestData implements DataFetcher
{
    public function fetch(array $config): array
    {
        return Http::withHeaders($config['headers'] ?? [])
            ->get($config['url'], $config['query'] ?? [])
            ->json();
    }
}
