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
        /** @var Model $model */
        $model = new $modelClass;

        $fillable = array_flip($model->getFillable());

        // Split row
        $fillableRow = array_intersect_key($row, $fillable);
        $remaining = array_diff_key($row, $fillable);

        // ✅ Upsert / create
        if ($uniqueBy && isset($fillableRow[$uniqueBy])) {
            $model = $modelClass::query()->updateOrCreate(
                [$uniqueBy => $fillableRow[$uniqueBy]],
                $fillableRow
            );
        } else {
            $model = $modelClass::query()->create($fillableRow);
        }

        // ✅ Abacs
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
}
