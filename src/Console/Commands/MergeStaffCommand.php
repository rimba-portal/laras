<?php

declare(strict_types=1);

namespace Rimba\Sync\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Description('Merge HRDB staff and LDAP accounts')]
#[Signature('rimba:usermerge
            {staffJson : Path to A (staff)}
            {ldapJson : Path to B (ldap)}')]
class MergeStaffCommand extends Command
{
    public function handle(): int
    {
        $staff = json_decode(
            File::get($this->argument('staffJson')),
            true
        );

        $ldap = json_decode(
            File::get($this->argument('ldapJson')),
            true
        );

        $result = [];

        $ldapIndex = collect($ldap)
            ->keyBy(fn ($row): string => sprintf(
                '%s|%s',
                $row['staff_no'] ?? '',
                strtolower($row['username'] ?? '')
            ));

        foreach ($staff as $row) {

            $key = sprintf(
                '%s|%s',
                $row['staff_no'] ?? '',
                strtolower($row['username'] ?? '')
            );

            $ldapUser = $ldapIndex->get($key);

            $row['isLDAP'] = $ldapUser !== null;
            $row['userType'] = 'staff';

            $result[] = $row;

            if ($ldapUser) {
                $ldapIndex->forget($key);
            }
        }

        foreach ($ldapIndex as $row) {

            $username = strtoupper(
                $row['username'] ?? ''
            );

            if (str_starts_with($username, 'ATM')) {

                $row['isLDAP'] = true;
                $row['userType'] = 'Intern';

                $result[] = $row;

                continue;
            }

            $row['isLDAP'] = true;
            $row['userType'] = 'LDAP';

            $result[] = $row;
        }

        $this->line(
            json_encode(
                array_values($result),
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            )
        );

        return self::SUCCESS;
    }
}
