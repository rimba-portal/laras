<?php

declare(strict_types=1);

namespace Rimba\Sync\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Rimba\Sync\Actions\SyncWorkforceApiConfig;

final class SyncWorkforceApiConfigJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $apiConfigId) {}

    public function handle(SyncWorkforceApiConfig $action): void
    {
        $action->execute($this->apiConfigId);
    }
}
