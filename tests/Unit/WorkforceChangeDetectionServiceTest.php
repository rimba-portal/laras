<?php

declare(strict_types=1);

namespace Rimba\Sync\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rimba\Sync\Services\WorkforceChangeDetectionService;

final class WorkforceChangeDetectionServiceTest extends TestCase
{
    public function test_it_detects_department_change(): void
    {
        config(['workforce-sync.tracked_fields' => ['department_uuid']]);
        $c = (new WorkforceChangeDetectionService)->detect(['department_uuid' => 'A'], ['department_uuid' => 'B']);
        $this->assertSame('department_uuid', $c[0]['field']);
    }
}
