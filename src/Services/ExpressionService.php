<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

class ExpressionService
{
    public function evaluate(array $expression, array $data): bool
    {
        $field = $expression['field'] ?? null;
        $operator = $expression['operator'] ?? '=';
        $value = $expression['value'] ?? null;

        $actual = data_get($data, $field);

        return match ($operator) {
            '=' => $actual == $value,
            '!=' => $actual != $value,
            '>' => $actual > $value,
            '<' => $actual < $value,
            '>=' => $actual >= $value,
            '<=' => $actual <= $value,
            'in' => in_array($actual, (array) $value),
            default => false,
        };
    }

    // 🚀 future-ready: AND/OR groups
    public function evaluateGroup(array $conditions, array $data, string $logic = 'AND'): bool
    {
        $results = array_map(fn (array $condition): bool => $this->evaluate($condition, $data),
            $conditions
        );

        return $logic === 'OR'
            ? in_array(true, $results)
            : ! in_array(false, $results);
    }
}
