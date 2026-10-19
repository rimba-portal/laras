<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Illuminate\Database\Eloquent\Model;
use Rimba\Attributing\Actions\SyncStaffAbacRolesAction;
use Rimba\People\Models\Staff;

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
         * Extract mapped relationship-style payloads before
         * filtering the ordinary model fields.
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
         * Person attributes belong in person_attributes,
         * not in the model's legacy attributes JSON column.
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
         * Persist the API mapping's "attributes" payload
         * through Sifat's canonical person-attribute method.
         */
        $this->applyPersonAttributes(
            model: $model,
            payloads: $seedPayloads,
        );

        /*
         * Staff synchronization is required after Staff-owned
         * person attributes have been persisted.
         *
         * JobPosition-owned attributes create role definitions
         * through PersonAttributeObserver. Staff inherits those
         * roles when its AgreementScope is created.
         */
        if (
            $model instanceof Staff
            && isset($seedPayloads['attributes'])
        ) {
            app(SyncStaffAbacRolesAction::class)
                ->execute($model);
        }

        /*
         * Retain legacy add_abac handling for unmatched values
         * only. It is not responsible for mapped attributes.
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
