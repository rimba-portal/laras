<?php

declare(strict_types=1);

namespace Rimba\Sync\Support;

final readonly class WorkforceSyncResult
{
    public function __construct(public string $sourceUuid, public string $result, public array $changes = [], public ?string $eventType = null, public ?string $error = null) {}
}
