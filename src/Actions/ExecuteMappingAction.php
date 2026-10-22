<?php

declare(strict_types=1);

namespace Rimba\Sync\Actions;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class ExecuteMappingAction
{
    public function execute(mixed $action, mixed $value, array $item, array $field): mixed
    {
        /*
         * PHP expression:
         *
         * 'do' => '@(...)'
         */
        if (is_string($action) && str_starts_with($action, '@')) {
            return $this->expression(expression: substr($action, 1), value: $value, item: $item, field: $field);
        }

        /*
         * Structured action.
         */
        if (is_array($action)) {
            if (isset($action['artisan']) || isset($action['command'])) {
                if (isset($action['transform'])) {
                    $value = $this->transform(transform: $action['transform'], value: $value, item: $item, field: $field);
                }

                return $this->artisan(command: $action['artisan'] ?? $action['command'], value: $value);
            }

            return $this->query(query: $action, value: $value, item: $item, field: $field);
        }

        /*
         * Legacy shorthand:
         *
         * artisan:command...
         */
        if (
            is_string($action)
            && str_starts_with($action, 'artisan:')
        ) {
            return $this->artisan(
                command: substr($action, 8),
                value: $value,
            );
        }

        throw new InvalidArgumentException(
            'Mapping action must be a query array, an artisan command string, or a PHP expression starting with @.'
        );
    }

    public function expression(
        string $expression,
        mixed $value,
        array $item,
        array $field,
    ): mixed {
        try {
            return eval('return '.$expression.';');
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException(
                sprintf(
                    'PHP expression action [%s] failed: %s',
                    $expression,
                    $throwable->getMessage()
                ),
                $throwable->getCode(),
                $throwable
            );
        }
    }

    protected function query(
        array $query,
        mixed $value,
        array $item,
        array $field,
    ): mixed {
        if (isset($query['query'])) {
            return $this->rawQuery(
                query: $query,
                value: $value,
            );
        }

        $modelClass = $query['model'] ?? null;

        if (
            ! is_string($modelClass)
            || ! class_exists($modelClass)
        ) {
            throw new InvalidArgumentException(
                "Query action requires a valid 'model' class."
            );
        }

        $queryBuilder = $modelClass::query();

        foreach (
            $query['where'] ?? [] as $column => $condition
        ) {
            if (
                is_string($condition)
                && str_contains($condition, '$value')
            ) {
                $condition = str_replace(
                    '$value',
                    (string) $value,
                    $condition
                );
            }

            $queryBuilder->where(
                $column,
                $condition
            );
        }

        if (isset($query['value'])) {
            return $queryBuilder->value(
                $query['value']
            );
        }

        if (isset($query['first'])) {
            $record = $queryBuilder->first();

            return $record?->getAttribute(
                $query['first']
            );
        }

        throw new InvalidArgumentException(
            "Query action must specify 'value', 'first', or 'query'."
        );
    }

    protected function rawQuery(
        array $query,
        mixed $value,
    ): mixed {
        $sql = $query['query'];

        $bindings = $query['bindings'] ?? [];

        $sql = str_replace(
            '$value',
            '?',
            $sql
        );

        $bindings = array_map(
            static fn (mixed $binding): mixed => $binding === '$value'
                ? $value
                : $binding,
            $bindings
        );

        $result = DB::selectOne(
            $sql,
            $bindings
        );

        if ($result === null) {
            return null;
        }

        if (isset($query['column'])) {
            return $result->{$query['column']};
        }

        return $result;
    }

    protected function artisan(
        string $command,
        mixed $value,
    ): ?string {
        $command = str_replace(
            '$value',
            escapeshellarg((string) $value),
            $command
        );

        $shell = PHP_BINARY
            .' artisan '
            .$command
            .' 2>&1';

        $output = trim(
            (string) shell_exec($shell)
        );

        return $output === ''
            ? null
            : $output;
    }

    protected function transform(
        mixed $transform,
        mixed $value,
        array $item,
        array $field,
    ): mixed {
        if (
            is_string($transform)
            && str_starts_with($transform, '@')
        ) {
            return $this->expression(
                expression: substr($transform, 1),
                value: $value,
                item: $item,
                field: $field,
            );
        }

        if (is_callable($transform)) {
            return $transform(
                $value,
                $item,
                $field
            );
        }

        return $transform;
    }
}
