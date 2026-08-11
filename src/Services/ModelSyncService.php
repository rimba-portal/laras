<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Illuminate\Database\Eloquent\Model;

class ModelSyncService
{
    public function sync(
        string $modelClass,
        ?string $uniqueBy,
        bool $addAbacs,
        array $row
    ): ?Model {
        /** @var Model $prototype */
        $prototype = new $modelClass;

        $fillable = array_flip($prototype->getFillable());

        $seedPayloads = $this->extractSeedPayloads($row);

        $fillableRow = array_intersect_key($row, $fillable);

        $remaining = array_diff_key($row, $fillable);

        unset($remaining['attributes'], $remaining['extra']);

        if ($uniqueBy && isset($fillableRow[$uniqueBy])) {
            /** @var Model $model */
            $model = $modelClass::query()->updateOrCreate(
                [$uniqueBy => $fillableRow[$uniqueBy]],
                $fillableRow
            );
        } else {
            /** @var Model $model */
            $model = $modelClass::query()->create($fillableRow);
        }

        $this->applySeedMappings($model, $modelClass, $seedPayloads);

        if ($addAbacs && $remaining !== [] && method_exists($model, 'setAbac')) {
            foreach ($remaining as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $model->setAbac($key, $value);
            }
        }

        return $model;
    }

    protected function extractSeedPayloads(array $row): array
    {
        $payloads = [];
        if (isset($row['attributes']) && is_array($row['attributes'])) {
            $payloads['attributes'] = $row['attributes'];
        }
        return $payloads;
    }

    protected function applySeedMappings(Model $model, string $modelClass, array $payloads): void
    {
        if ($payloads === []) {
            return;
        }

        if (! method_exists($modelClass, 'seedMappings')) {
            return;
        }

        $mappings = $modelClass::seedMappings();

        foreach ($payloads as $sourceKey => $payload) {
            if (! isset($mappings[$sourceKey])) {
                continue;
            }

            $method = $mappings[$sourceKey];

            if (! is_string($method)) {
                continue;
            }

            if (! method_exists($model, $method)) {
                continue;
            }

            $model->{$method}($payload);
        }
    }
}
