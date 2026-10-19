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
        array $row,
    ): ?Model {
        /** @var Model $prototype */
        $prototype = new $modelClass;

        /*
         * Extract mapped person attributes before filtering
         * ordinary model fields.
         */
        $seedPayloads = $this->extractSeedPayloads($row);

        $fillable = array_flip(
            $prototype->getFillable()
        );

        $fillableRow = array_intersect_key(
            $row,
            $fillable
        );

        /*
         * attributes are stored in person_attributes.
         */
        unset($fillableRow['attributes']);

        $remaining = array_diff_key(
            $row,
            $fillable
        );

        unset(
            $remaining['attributes'],
            $remaining['extra'],
        );

        if (
            $uniqueBy
            && array_key_exists($uniqueBy, $fillableRow)
        ) {
            /** @var Model $model */
            $model = $modelClass::query()->updateOrCreate(
                [
                    $uniqueBy => $fillableRow[$uniqueBy],
                ],
                $fillableRow,
            );
        } else {
            /** @var Model $model */
            $model = $modelClass::query()->create(
                $fillableRow
            );
        }

        /*
         * Persist explicitly mapped person attributes.
         */
        $this->applyPersonAttributes(
            model: $model,
            payloads: $seedPayloads,
        );

        /*
         * Legacy unmatched-value ABAC handling.
         */
        if (
            $addAbacs
            && $remaining !== []
            && method_exists($model, 'setAbac')
        ) {
            foreach ($remaining as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                $model->setAbac(
                    $key,
                    $value,
                );
            }
        }

        return $model;
    }

    protected function extractSeedPayloads(
        array $row
    ): array {
        $payloads = [];

        if (
            isset($row['attributes'])
            && is_array($row['attributes'])
        ) {
            $payloads['attributes'] =
                $row['attributes'];
        }

        return $payloads;
    }

    protected function applyPersonAttributes(
        Model $model,
        array $payloads,
    ): void {
        $attributes = $payloads['attributes'] ?? null;

        if (
            ! is_array($attributes)
            || $attributes === []
        ) {
            return;
        }

        if (! method_exists(
            $model,
            'syncPersonAttributes'
        )) {
            return;
        }

        $model->syncPersonAttributes(
            $attributes
        );
    }
}
