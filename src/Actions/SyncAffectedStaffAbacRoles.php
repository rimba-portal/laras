<?php

declare(strict_types=1);

namespace Rimba\Sync\Actions;

use App\ProcessTrace;
use Rimba\Attributing\Actions\SyncStaffAbacRolesAction;
use Rimba\People\Models\Staff;

class SyncAffectedStaffAbacRoles
{
    public function __construct(
        protected SyncStaffAbacRolesAction $syncStaffAbacRoles,
    ) {}

    public function execute(array $staffIds): void
    {
        if ($staffIds === []) {
            return;
        }

        $chunkSize = max(
            1,
            (int) config(
                'bites.sync.chunk_size',
                500
            )
        );

        $chunkIndex = 0;

        Staff::query()
            ->whereKey($staffIds)
            ->with([
                'user',
                'personAttributes',
                'jobPosition.personAttributes',
            ])
            ->chunkById(
                $chunkSize,
                function ($staff) use (
                    &$chunkIndex
                ): void {
                    $chunkIndex++;

                    $key =
                        "mapping.sync-staff-abac.chunk.{$chunkIndex}";

                    // ProcessTrace::start($key);

                    foreach ($staff as $member) {
                        $this->syncStaffAbacRoles
                            ->execute($member);
                    }

                    // ProcessTrace::end($key);

                    unset($staff);

                    if (gc_enabled()) {
                        gc_collect_cycles();
                    }
                }
            );
    }
}
