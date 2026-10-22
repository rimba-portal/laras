<?php

declare(strict_types=1);

namespace Rimba\Sync\Actions;

class ResolveMappedValue
{
    public function __construct(
        protected ExecuteMappingAction $executeMappingAction,
    ) {}

    public function execute(
        array $field,
        array $item,
        array &$usedKeys,
    ): mixed {
        /*
         * Explicit constant value.
         *
         * Important: array_key_exists() means
         * 'value' => null remains valid.
         */
        if (array_key_exists('value', $field)) {
            return $field['value'];
        }

        $from = $field['from'] ?? null;

        $value = data_get(
            $item,
            $from
        );

        if (is_string($from)) {
            $usedKeys[] = $from;
        }

        /*
         * Execute do/action before regex,
         * matching the current MappingService behavior.
         */
        if (isset($field['do'])) {
            $value = $this->executeMappingAction->execute(
                action: $field['do'],
                value: $value,
                item: $item,
                field: $field,
            );
        }

        /*
         * Existing regex mapping support.
         */
        if (
            isset($field['regex'])
            && is_string($value)
        ) {
            if (
                str_starts_with(
                    $field['regex'],
                    '@'
                )
            ) {
                $value = $this->executeMappingAction->expression(
                    expression: substr(
                        $field['regex'],
                        1
                    ),
                    value: $value,
                    item: $item,
                    field: $field,
                );
            } else {
                $value = preg_replace(
                    $field['regex'],
                    '$1',
                    $value
                ) ?? $value;
            }
        }

        return $value;
    }
}
