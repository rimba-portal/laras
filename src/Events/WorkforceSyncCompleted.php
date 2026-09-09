<?php

declare(strict_types=1);

namespace Rimba\Sync\Events;

final readonly class WorkforceSyncCompleted
{
    public function __construct(public int $runId) {}
}
