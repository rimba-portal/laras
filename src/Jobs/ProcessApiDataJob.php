<?php

declare(strict_types=1);

namespace Rimba\Sync\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Rimba\Sync\Models\ApiData;
use Rimba\Sync\Services\ProcessingService;

class ProcessApiDataJob implements ShouldQueue
{
    use Queueable;

    public ApiData $data;

    public function __construct(ApiData $data)
    {
        $this->data = $data;
    }

    public function handle(): void
    {
        app(ProcessingService::class)->process($this->data);
    }

    /**
     * ✅ Optional but recommended defaults
     */
    public $tries = 3;

    public $timeout = 120;
}
