<?php

declare(strict_types=1);

namespace Rimba\Sync\Services;

use Rimba\Wfm\Enums\WorkforceEventType;

final class WorkforceEventClassificationService
{
    public function classify(?array $before, array $after, array $changes): ?WorkforceEventType
    {
        if ($before === null) {
            return WorkforceEventType::Hired;
        } $fields = array_column($changes, 'field');
        $old = (string) ($before['status'] ?? '');
        $new = (string) ($after['status'] ?? '');
        if (in_array($new, config('workforce-sync.separated_statuses', []), true) && ! in_array($old, config('workforce-sync.separated_statuses', []), true)) {
            return WorkforceEventType::Separated;
        }

        if (in_array($new, config('workforce-sync.active_statuses', []), true) && in_array($old, config('workforce-sync.separated_statuses', []), true)) {
            return WorkforceEventType::Rehired;
        }

        if (in_array('job_grade', $fields, true)) {
            $cmp = $this->grade($after['job_grade'] ?? null) <=> $this->grade($before['job_grade'] ?? null);
            if ($cmp > 0) {
                return WorkforceEventType::Promoted;
            } if ($cmp < 0) {
                return WorkforceEventType::Demoted;
            }
        }

        if (in_array('department_uuid', $fields, true)) {
            return WorkforceEventType::Transferred;
        }

        if (in_array('organization_uuid', $fields, true)) {
            return WorkforceEventType::OrganizationChanged;
        }

        if (in_array('job_title_code', $fields, true) || in_array('job_title_name', $fields, true)) {
            return WorkforceEventType::Reassigned;
        }

        if (in_array('manager_uuid', $fields, true)) {
            return WorkforceEventType::ReportingChanged;
        }

        if (in_array('shift_code', $fields, true)) {
            return WorkforceEventType::ShiftChanged;
        }

        return $changes ? WorkforceEventType::Corrected : null;
    }

    private function grade(mixed $v): int
    {
        if ($v === null) {
            return 0;
        } preg_match('/\d+/', (string) $v, $m);

        return isset($m[0]) ? (int) $m[0] : 0;
    }
}
