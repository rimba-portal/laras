<?php

declare(strict_types=1);

namespace Rimba\Sync\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rimba\Sync\Services\HrdbWorkforceNormalizer;

final class HrdbWorkforceNormalizerTest extends TestCase
{
    public function test_it_normalizes_without_copying_password(): void
    {
        $d = (new HrdbWorkforceNormalizer)->normalize(['uuid' => 'abc', 'lastname' => 'Staff A', 'workcode' => '1', 'status' => '1', 'password' => 'secret', 'email' => 'ABC123@amkor.com', 'field5' => 'ATM-1280-Manufacturing 4']);
        $this->assertSame('abc', $d['source_uuid']);
        $this->assertSame('Manufacturing 4', $d['department_name']);
        $this->assertNull($d['email']);
        $this->assertArrayNotHasKey('password', $d);
    }
}
