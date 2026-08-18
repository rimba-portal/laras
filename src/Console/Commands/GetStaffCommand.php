<?php

declare(strict_types=1);

namespace Rimba\Sync\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Description('Export all HR staff records as JSON')]
#[Signature('rimba:getstaff')]
class GetStaffCommand extends Command
{
    public function handle(): int
    {
        $rows = DB::connection('external_hr')
            ->select(<<<'SQL'
                SELECT
                    r.workcode                  AS staff_no,
                    LOWER(r.loginid)           AS username,
                    r.email                    AS email,
                    r.lastname                 AS name,
                    c.field9                   AS title,
                    d.uuid                     AS department_uuid
                FROM HrmResource r
                LEFT JOIN HrmDepartment d
                    ON r.departmentid = d.id
                LEFT JOIN cus_fielddata c
                    ON r.id = c.id
                WHERE r.workcode IS NOT NULL
                  AND r.loginid IS NOT NULL
                ORDER BY r.workcode
            SQL);

        $data = collect($rows)
            ->map(fn ($row): array => [
                'staff_no' => $row->staff_no,
                'username' => $row->username,
                'email' => $row->email,
                'name' => $row->name,
                'title' => $row->title,
                'department_uuid' => $row->department_uuid,
            ])
            ->values()
            ->all();

        $this->line(
            json_encode(
                $data,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            )
        );

        return self::SUCCESS;
    }
}
