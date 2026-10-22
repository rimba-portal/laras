<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use App\ProcessTrace;
use Rimba\Sync\Actions\MapEntity;
use Rimba\Sync\Actions\SyncAffectedStaffAbacRoles;
use Rimba\Sync\Models\ApiData;

class MappingService
{
    public function __construct(
        protected MapEntity $mapEntity,
        protected SyncAffectedStaffAbacRoles $syncAffectedStaffAbacRoles,
    ) {}

    public function run(ApiData $data): void
    {
        $affectedStaffIds = [];

        // ProcessTrace::start('mapping.entities');

        foreach ($data->apiConfig->mapping as $index => $entity) {
            $model = $entity['model'] ?? 'unknown';

            $key = sprintf(
                'entity.%d.%s',
                $index,
                class_basename($model)
            );

            // ProcessTrace::start($key);

            $this->mapEntity->execute(
                entity: $entity,
                payload: $data->payload,
                affectedStaffIds: $affectedStaffIds,
                traceKey: $key,
            );

            // ProcessTrace::end($key);
        }

        // ProcessTrace::end('mapping.entities');

        // ProcessTrace::start('mapping.sync-staff-abac');

        $this->syncAffectedStaffAbacRoles->execute(
            array_keys($affectedStaffIds)
        );

        // ProcessTrace::end('mapping.sync-staff-abac');
    }
}
