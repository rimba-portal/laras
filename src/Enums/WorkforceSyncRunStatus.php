<?php

declare(strict_types=1);

namespace Rimba\Sync\Enums;

enum WorkforceSyncRunStatus: string
{
    case Running = 'running';
    case Completed = 'completed';
    case CompletedWithErrors = 'completed_with_errors';
    case Failed = 'failed';
}
