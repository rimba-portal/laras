<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use InvalidArgumentException;
use Rimba\Sync\Contracts\WorkforceRowNormalizer;

final class HrdbWorkforceNormalizer implements WorkforceRowNormalizer
{
    public function normalize(array $r): array
    {
        $uuid = trim((string) ($r['uuid'] ?? ''));
        if ($uuid === '') {
            throw new InvalidArgumentException('HRDB row has no uuid.');
        }

        $dismiss = $this->null($r['DISMISSDATE'] ?? null);
        $end = $this->null($r['enddate'] ?? null);
        $status = $dismiss ? 'separated' : (config('workforce-sync.status_map')[(string) ($r['status'] ?? '')] ?? 'inactive');

        return [
            'source_uuid' => $uuid, 'name' => trim((string) ($r['lastname'] ?? '')), 'staff_no' => $this->null($r['workcode'] ?? null),
            'status' => $status, 'employment_type' => $this->null($r['resourcetype'] ?? null) ?? 'FTE',
            'company_start_date' => $this->null($r['companystartdate'] ?? null), 'work_start_date' => $this->null($r['workstartdate'] ?? null),
            'end_date' => $end, 'dismiss_date' => $dismiss, 'organization_uuid' => $this->null($r['organization_uuid'] ?? null),
            'department_uuid' => $this->null($r['department_uuid'] ?? null), 'department_name' => $this->label($r['field5'] ?? null),
            'job_title_code' => $this->null($r['jobtitle'] ?? null), 'job_title_name' => $this->null($r['field9'] ?? null) ?? $this->null($r['job_title_name'] ?? null),
            'job_grade' => $this->null($r['field3'] ?? null), 'manager_uuid' => $this->null($r['manager_uuid'] ?? null),
            'shift_code' => $this->null($r['field8'] ?? null), 'email' => $this->validEmail($r['email'] ?? null), 'login_id' => $this->null($r['loginid'] ?? null),
            'source_modified_at' => $this->null($r['modified'] ?? null) ?? $this->null($r['lastmoddate'] ?? null),
            'attributes' => array_filter(['hrdb_id' => $this->null($r['id'] ?? null), 'department_id' => $this->null($r['departmentid'] ?? null), 'manager_id' => $this->null($r['managerid'] ?? null), 'job_title_raw' => $this->null($r['job_title_name'] ?? null), 'cost_center' => $this->null($r['field4'] ?? null), 'division' => $this->label($r['field6'] ?? null), 'sequence' => $this->null($r['seqorder'] ?? null)], fn (?string $v): bool => $v !== null),
        ];
    }

    private function null(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        } $v = trim((string) $v);

        return $v === '' ? null : $v;
    }

    private function label(mixed $v): ?string
    {
        $v = $this->null($v);
        if (! $v) {
            return null;
        } $p = explode('-', $v, 3);

        return count($p) === 3 ? trim($p[2]) : $v;
    }

    private function validEmail(mixed $v): ?string
    {
        $v = $this->null($v);
        if (! $v || ! filter_var($v, FILTER_VALIDATE_EMAIL) || strtolower($v) === 'abc123@amkor.com') {
            return null;
        }

        return strtolower($v);
    }
}
