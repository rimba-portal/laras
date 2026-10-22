<?php

declare(strict_types=1);

namespace Rimba\Sync\Actions;

use App\Support\ProcessTrace;
use Illuminate\Database\Eloquent\Model;
use Rimba\People\Models\Staff;
use Rimba\Sync\Services\ModelSyncService;

class MapEntity
{
    public function __construct(
        protected ResolveMappedValue $resolveMappedValue,
        protected ModelSyncService $modelSyncService,
    ) {}

    public function execute(
        array $entity,
        array $payload,
        array &$affectedStaffIds,
        ?Model $parent = null,
        ?string $traceKey = null,
    ): void {
        $items = $this->resolveItems(
            $entity,
            $payload
        );

        if ($items === null) {
            return;
        }

        $chunkSize = max(
            1,
            (int) config(
                'bites.sync.chunk_size',
                500
            )
        );

        foreach (
            array_chunk($items, $chunkSize) as $chunkIndex => $chunk
        ) {
            $chunkKey = $traceKey !== null
                ? "{$traceKey}.chunk.".($chunkIndex + 1)
                : null;

            if ($chunkKey !== null) {
                // ProcessTrace::start($chunkKey);
            }

            $this->processChunk(
                entity: $entity,
                items: $chunk,
                affectedStaffIds: $affectedStaffIds,
                parent: $parent,
                traceKey: $chunkKey,
            );

            if ($chunkKey !== null) {
                // ProcessTrace::end($chunkKey);
            }

            unset($chunk);

            if (gc_enabled()) {
                gc_collect_cycles();
            }
        }
    }

    protected function resolveItems(
        array $entity,
        array $payload,
    ): ?array {
        $path = $entity['path'] ?? '';

        $items = $path === ''
            ? $payload
            : data_get($payload, $path);

        if (! is_array($items)) {
            return null;
        }

        if (! ($entity['many'] ?? false)) {
            return [$items];
        }

        return $items;
    }

    protected function processChunk(
        array $entity,
        array $items,
        array &$affectedStaffIds,
        ?Model $parent = null,
        ?string $traceKey = null,
    ): void {
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $row = [];
            $usedKeys = [];

            /*
             * Normal model fields.
             */
            foreach ($entity['fields'] ?? [] as $field) {
                $value = $this->resolveMappedValue->execute(
                    field: $field,
                    item: $item,
                    usedKeys: $usedKeys,
                );

                if (isset($field['to'])) {
                    $row[$field['to']] = $value;
                }
            }

            /*
             * Dynamic attributes.
             */
            foreach ($entity['attributes'] ?? [] as $attribute) {
                $value = $this->resolveMappedValue->execute(
                    field: $attribute,
                    item: $item,
                    usedKeys: $usedKeys,
                );

                $row['attributes'][
                    $attribute['key']
                ] = $value;
            }

            /*
             * Skip invalid/unwanted rows.
             */
            if ($this->shouldSkip($entity, $row)) {
                continue;
            }

            /*
             * Child entity receives its parent's primary key.
             *
             * Example:
             *
             * Agreement
             *     -> AgreementScope.agreement_id
             */
            if (
                $parent instanceof Model
                && isset($entity['foreign_key'])
            ) {
                $row[
                    $entity['foreign_key']
                ] = $parent->getKey();
            }

            /*
             * Existing ModelSyncService contract:
             *
             * sync(): ?Model
             */
            $model = $this->modelSyncService->sync(
                modelClass: $entity['model'],
                uniqueBy: $entity['unique_by'] ?? null,
                addAbacs: $entity['add_abac'] ?? false,
                row: $row,
            );

            if (! $model instanceof Model) {
                continue;
            }

            /*
             * Track Staff only when the Staff record was
             * actually created or changed.
             */
            if (
                $model instanceof Staff
                && (
                    $model->wasRecentlyCreated
                    || $model->wasChanged()
                )
            ) {
                $id = $model->getKey();

                if ($id !== null) {
                    $affectedStaffIds[$id] = true;
                }
            }

            /*
             * Optionally write child ID back to parent.
             */
            if (
                $parent instanceof Model
                && isset($entity['parent_key'])
            ) {
                $this->syncParentKey(
                    parent: $parent,
                    parentKey: $entity['parent_key'],
                    child: $model,
                );
            }

            /*
             * Recursively process children.
             *
             * The synchronized model becomes the parent.
             */
            foreach (
                $entity['children'] ?? [] as $childIndex => $child
            ) {
                $childModel = class_basename(
                    $child['model'] ?? 'unknown'
                );

                $childTraceKey = $traceKey !== null
                    ? "{$traceKey}.child.{$childIndex}.{$childModel}"
                    : null;

                $this->execute(
                    entity: $child,
                    payload: $item,
                    affectedStaffIds: $affectedStaffIds,
                    parent: $model,
                    traceKey: $childTraceKey,
                );
            }
        }
    }

    protected function shouldSkip(
        array $entity,
        array $row,
    ): bool {
        $rule = $entity['skip_if'] ?? null;

        if (! is_array($rule)) {
            return false;
        }

        $field = $rule['field'] ?? null;
        $minLength = $rule['min_length'] ?? null;

        if (
            ! is_string($field)
            || $minLength === null
        ) {
            return false;
        }

        if (! array_key_exists($field, $row)) {
            return false;
        }

        return mb_strlen(
            trim((string) ($row[$field] ?? ''))
        ) < (int) $minLength;
    }

    protected function syncParentKey(
        Model $parent,
        string $parentKey,
        Model $child,
    ): void {
        if (
            $parent->getAttribute($parentKey)
            === $child->getKey()
        ) {
            return;
        }

        $parent->forceFill([
            $parentKey => $child->getKey(),
        ])->save();
    }
}
