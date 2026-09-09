<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Rimba\People\Models\Staff;

final class StaffSynchronizationService
{
    public function sync(array $d, ?int $orgCorpId, ?int $orgUnitId): array
    {
        $staff = Staff::firstOrNew(['uuid' => $d['source_uuid']]);
        $created = ! $staff->exists;
        $attrs = array_merge($staff->attributes ?? [], array_filter(array_merge($d['attributes'] ?? [], ['email' => $d['email'], 'login_id' => $d['login_id'], 'company_start_date' => $d['company_start_date'], 'work_start_date' => $d['work_start_date'], 'end_date' => $d['end_date'], 'dismiss_date' => $d['dismiss_date'], 'source' => 'hrdb']), fn ($v): bool => $v !== null));
        $staff->fill(['org_corp_id' => $orgCorpId, 'org_unit_id' => $orgUnitId, 'type' => $d['employment_type'], 'status' => $d['status'], 'name' => $d['name'], 'staff_no' => $d['staff_no'], 'attributes' => $attrs]);
        $dirty = $staff->isDirty();
        $staff->save();

        return [$staff, $created ? 'created' : ($dirty ? 'updated' : 'unchanged')];
    }
}
