<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Rimba\Hrm\Models\JobTitle;
use Rimba\Organization\Models\OrgCorp;
use Rimba\Organization\Models\OrgUnit;
use Rimba\People\Models\Staff;
use Rimba\Position\Models\JobPosition;
use Rimba\Time\Models\Shift;

final class WorkforceMasterDataService
{
    public function resolve(array $d): array
    {
        $corp = $d['organization_uuid'] ? OrgCorp::updateOrCreate(['uuid' => $d['organization_uuid']], ['name' => $d['organization_uuid'], 'code' => $d['organization_uuid'], 'type' => 'company', 'attributes' => ['source' => 'hrdb']]) : null;
        $unit = $d['department_uuid'] ? OrgUnit::updateOrCreate(['uuid' => $d['department_uuid']], ['org_corp_id' => $corp?->id, 'name' => $d['department_name'] ?? $d['department_uuid'], 'code' => $d['department_uuid'], 'attributes' => ['source' => 'hrdb']]) : null;
        $title = null;
        if ($d['job_title_name'] || $d['job_title_code']) {
            $title = JobTitle::updateOrCreate(['uuid' => $this->uuid('hrdb-job-title:'.($d['job_title_code'] ?? $d['job_title_name']))], ['title' => $d['job_title_name'] ?? $d['job_title_code'], 'jobgrade' => $d['job_grade'], 'attributes' => ['source_code' => $d['job_title_code'], 'source' => 'hrdb']]);
        }

        $pos = null;
        if ($title || $unit) {
            $pos = JobPosition::updateOrCreate(['uuid' => $this->uuid('hrdb-position:'.($d['department_uuid'] ?? 'none').':'.($d['job_title_code'] ?? $d['job_title_name'] ?? 'none'))], ['job_title_id' => $title?->id, 'org_unit_id' => $unit?->id, 'level' => $d['job_grade'], 'status' => 'active', 'title' => $d['job_title_name'], 'attributes' => ['source' => 'hrdb']]);
        }

        $manager = $d['manager_uuid'] ? Staff::where('uuid', $d['manager_uuid'])->first() : null;
        $shift = $d['shift_code'] ? Shift::firstOrCreate(['org_unit_id' => $unit?->id, 'name' => $d['shift_code']], ['type' => 'hrdb', 'description' => 'Imported from HRDB', 'attributes' => ['source_code' => $d['shift_code'], 'source' => 'hrdb']]) : null;

        return ['org_corp_id' => $corp?->id, 'org_unit_id' => $unit?->id, 'job_position_id' => $pos?->id, 'manager_staff_id' => $manager?->id, 'shift_id' => $shift?->id];
    }

    private function uuid(string $v): string
    {
        $h = md5($v);

        return substr($h, 0, 8).'-'.substr($h, 8, 4).'-5'.substr($h, 13, 3).'-a'.substr($h, 17, 3).'-'.substr($h, 20, 12);
    }
}
