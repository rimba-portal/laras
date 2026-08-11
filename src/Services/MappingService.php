<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Illuminate\Support\Facades\DB;
use Rimba\Sync\Models\ApiData;

class MappingService
{
    public function run(ApiData $data): void
    {
        foreach ($data->apiConfig->mapping as $entity) {
            $this->processEntity($entity, $data->payload);
        }
    }

    protected function processEntity(array $entity, array $payload, $parent = null): void
    {
        $path = $entity['path'] ?? '';
        $items = $path === '' ? $payload : data_get($payload, $path);

        if (! is_array($items)) {
            return;
        }

        if (! ($entity['many'] ?? false)) {
            $items = [$items];
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $row = [];
            $usedKeys = [];

            foreach ($entity['fields'] as $field) {
                $value = $this->resolveFieldValue($field, $item, $usedKeys);

                if (isset($field['regex']) && is_string($value)) {
                    if (str_starts_with($field['regex'], '@')) {
                        $value = $this->executePhpExpression(substr($field['regex'], 1), $value, $item, $field);
                    } else {
                        $value = preg_replace($field['regex'], '$1', $value) ?? $value;
                    }
                }

                // ✅ attribute mapping
                foreach ($entity['attributes'] ?? [] as $attribute) {
                    $value = $this->resolveFieldValue(
                        $attribute,
                        $item,
                        $usedKeys
                    );
                    $key = $attribute['key'];
                    $row['attributes'][$key] = $value;
                }

                // ✅ normal column mapping
                if (isset($field['to'])) {
                    $row[$field['to']] = $value;
                }
            }

            if (isset($entity['skip_if'])) {
                $rule = $entity['skip_if'];

                if (
                    isset($rule['field'], $rule['min_length']) &&
                    isset($row[$rule['field']]) &&
                    mb_strlen(trim((string) $row[$rule['field']])) < $rule['min_length']
                ) {
                    continue;
                }
            }

            if ($parent && isset($entity['foreign_key'])) {
                // child foreign keys link the child record back to the parent
                $row[$entity['foreign_key']] = $parent->id;
            }

            $remaining = array_diff_key(
                $item,
                array_flip($usedKeys)
            );

            $model = app(ModelSyncService::class)->sync(
                modelClass: $entity['model'],
                uniqueBy: $entity['unique_by'] ?? null,
                addAbacs: $entity['add_abac'] ?? false,
                row: $row
            );

            if ($parent && isset($entity['parent_key'])) {
                $parent->forceFill([$entity['parent_key'] => $model->id])->save();
            }

            foreach ($entity['children'] ?? [] as $child) {
                $this->processEntity($child, $item, $model);
            }
        }
    }

    protected function resolveFieldValue(array $field, array $item, array &$usedKeys)
    {
        if (array_key_exists('value', $field)) {
            return $field['value'];
        }

        if (isset($field['do'])) {
            $input = data_get($item, $field['from'] ?? null);

            if (isset($field['from']) && is_string($field['from'])) {
                $usedKeys[] = $field['from'];
            }

            return $this->executeFieldAction($field['do'], $input, $item, $field);
        }

        $value = data_get($item, $field['from'] ?? null);

        if (isset($field['from']) && is_string($field['from'])) {
            $usedKeys[] = $field['from'];
        }

        return $value;
    }

    protected function executeFieldAction(mixed $action, mixed $value, array $item, array $field)
    {
        if (is_string($action) && str_starts_with($action, '@')) {
            return $this->executePhpExpression(substr($action, 1), $value, $item, $field);
        }

        if (is_array($action)) {
            if (isset($action['artisan']) || isset($action['command'])) {
                $command = $action['artisan'] ?? $action['command'];

                if (isset($action['transform'])) {
                    $value = $this->resolveTransform($action['transform'], $value, $item, $field);
                }

                return $this->executeArtisanCommand($command, $value);
            }

            return $this->executeQueryAction($action, $value, $item, $field);
        }

        if (is_string($action) && str_starts_with($action, 'artisan:')) {
            return $this->executeArtisanCommand(substr($action, 8), $value);
        }

        throw new \InvalidArgumentException('Mapping action must be a query array, an artisan command string, or a PHP expression starting with @.');
    }

    protected function resolveTransform(mixed $transform, mixed $value, array $item, array $field)
    {
        if (is_string($transform) && str_starts_with($transform, '@')) {
            dd($this);

            return $this->executePhpExpression(substr($transform, 1), $value, $item, $field);
        }

        if (is_callable($transform)) {
            return $transform($value, $item, $field);
        }

        return $transform;
    }

    protected function executeQueryAction(array $query, mixed $value, array $item, array $field)
    {
        if (isset($query['query'])) {
            return $this->executeRawQuery($query, $value, $item, $field);
        }

        $modelClass = $query['model'] ?? null;
        if (! $modelClass || ! class_exists($modelClass)) {
            throw new \InvalidArgumentException("Query action requires a valid 'model' class.");
        }

        $queryBuilder = $modelClass::query();

        if (isset($query['where'])) {
            foreach ($query['where'] as $column => $condition) {
                if (is_string($condition) && str_contains($condition, '$value')) {
                    $condition = str_replace('$value', $value, $condition);
                }

                $queryBuilder->where($column, $condition);
            }
        }

        if (isset($query['value'])) {
            return $queryBuilder->value($query['value']);
        }

        if (isset($query['first'])) {
            $record = $queryBuilder->first();

            return $record ? $record->{$query['first']} : null;
        }

        throw new \InvalidArgumentException("Query action must specify 'value', 'first', or 'query'.");
    }

    protected function executeRawQuery(array $query, mixed $value, array $item, array $field)
    {
        $sql = $query['query'];
        $bindings = $query['bindings'] ?? [];

        // Replace $value in SQL and bindings
        $sql = str_replace('$value', '?', $sql);
        $bindings = array_map(fn ($b) => $b === '$value' ? $value : $b, $bindings);

        $result = DB::selectOne($sql, $bindings);

        if (! $result) {
            return null;
        }

        if (isset($query['column'])) {
            return $result->{$query['column']};
        }

        return $result;
    }

    protected function executeArtisanCommand(string $command, mixed $value): ?string
    {
        $command = str_replace('$value', escapeshellarg((string) $value), $command);
        $shell = PHP_BINARY.' artisan '.$command.' 2>&1';

        $output = trim(shell_exec($shell));

        return $output === '' ? null : $output;
    }

    protected function executePhpExpression(string $expression, mixed $value, array $item, array $field)
    {
        try {
            return eval('return '.$expression.';');
        } catch (\Throwable $throwable) {
            throw new \InvalidArgumentException(sprintf('PHP expression action [%s] failed: ', $expression).$throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
