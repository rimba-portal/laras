<?php

declare(strict_types=1);

return [
    'source' => 'hrdb',
    'source_key' => 'uuid',
    'chunk_size' => 500,
    'skip_unchanged' => true,
    'store_normalized_payload' => true,
    'tracked_fields' => [
        'name', 'staff_no', 'status', 'employment_type', 'company_start_date', 'work_start_date', 'end_date', 'dismiss_date',
        'organization_uuid', 'department_uuid', 'department_name', 'job_title_code', 'job_title_name', 'job_grade',
        'manager_uuid', 'shift_code', 'email', 'login_id',
    ],
    'status_map' => ['1' => 'active', '5' => 'separated'],
    'active_statuses' => ['active'],
    'separated_statuses' => ['separated', 'terminated', 'inactive'],
    'event_precedence' => [
        'separated', 'rehired', 'hired', 'promoted', 'demoted', 'transferred', 'organization_changed',
        'reassigned', 'reporting_changed', 'shift_changed', 'agreement_changed', 'corrected',
    ],
];
