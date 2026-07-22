<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Rimba\Sync\Models\ApiData;

class ProcessingService
{
    public function process(ApiData $data): void
    {
        dump(sprintf('Processing API data ID: %s with config: %s', $data->id, $data->apiConfig->name));
        try {
            app(MappingService::class)->run($data);
            $data->markProcessed();
        } catch (\Throwable $throwable) {
            $data->markFailed($throwable->getMessage());
            throw $throwable;
        }
    }
}
