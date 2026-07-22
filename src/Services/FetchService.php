<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Rimba\Base\Actions\PutFingerPrint;
use Rimba\Sync\Actions\FetchDatabaseData;
use Rimba\Sync\Actions\FetchRestData;
use Rimba\Sync\Models\ApiConfig;
use Rimba\Sync\Models\ApiData;

class FetchService
{
    public function fetch(ApiConfig $config): void
    {
        $fetcher = match ($config->source_type) {
            'rest' => new FetchRestData,
            'database' => new FetchDatabaseData,
        };

        $data = $fetcher->fetch($config->source_config);

        $items = data_get($data, $config->data_path ?? 'data', $data);

        // foreach ($items as $item) {
        ApiData::firstOrCreate(
            [
                'api_config_id' => $config->id,
                'fingerprint' => PutFingerPrint::make((array) $items),
            ],
            [
                'payload' => (array) $items,
                'status' => 'pending',
            ]
        );
        // }
    }
}
