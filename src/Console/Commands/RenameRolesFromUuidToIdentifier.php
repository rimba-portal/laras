<?php

declare(strict_types=1);

namespace Rimba\Sync\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Rimba\Organization\Models\OrgCorp;
use Rimba\Organization\Models\OrgTeam;
use Rimba\Organization\Models\OrgUnit;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Description('Step 2: Replace trailing UUIDs in Spatie role names with readable identifiers from OrgTeam, OrgUnit, and OrgCorp')]
#[Signature('rimba:rename-uuid-roles {--dry-run : Execute dry-run mapping verification without persisting changes}')]
class RenameRolesFromUuidToIdentifier extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🧪 DRY RUN MODE ACTIVE - No changes will be written to the database.');
        }

        // The Configuration Matrix remains completely generic and unaltered
        $configurations = [
            [
                'prefix' => 'staff◇team◇',
                'model' => OrgTeam::class,
                'getter' => 'nick',
            ],
            [
                'prefix' => 'job_position◇department◇',
                'model' => OrgUnit::class,
                'getter' => 'nick',
            ],
            [
                'prefix' => 'job_position◇organization◇',
                'model' => OrgCorp::class,
                'getter' => 'nick',
            ],
        ];

        $anyChangesMade = false;

        foreach ($configurations as $configuration) {
            $prefix = $configuration['prefix'];
            $modelClass = $configuration['model'];
            $getter = $configuration['getter'];

            $this->newLine();
            $this->info("🔍 Processing Prefix Pattern: [{$prefix}] matching target [{$modelClass}]");

            $roles = Role::where('name', 'like', "{$prefix}%")->get();

            if ($roles->isEmpty()) {
                $this->line("ℹ️ No roles matched pattern prefix '{$prefix}'");

                continue;
            }

            foreach ($roles as $role) {
                $uuid = Str::after($role->name, $prefix);

                // Universal Polymorphic Query Locator:
                // Checks if the model has a unified custom 'scopeWhereUuid' method implemented
                $query = $modelClass::query();

                if (method_exists($modelClass, 'WhereUuid')) {
                    $record = $query->whereUuid($uuid)->first();
                } else {
                    $record = $query->where('uuid', $uuid)->first();
                }

                if (! $record) {
                    $this->error("❌ Record not found inside table for model [{$modelClass}] with UUID: {$uuid} (Role: {$role->name})");

                    continue;
                }

                $replacementValue = method_exists($record, $getter)
                    ? $record->{$getter}()
                    : $record->{$getter};

                if (empty($replacementValue)) {
                    $this->error("❌ Target identification resolved value is completely blank for record ID {$record->id} via [{$getter}]");

                    continue;
                }

                $newRoleName = "{$prefix}{$replacementValue}";

                if ($role->name === $newRoleName) {
                    $this->line("✅ Skipping [{$role->name}] - Already matches the target convention.");

                    continue;
                }

                $duplicateExists = Role::where('name', $newRoleName)
                    ->where('guard_name', $role->guard_name)
                    ->where('id', '!=', $role->id)
                    ->exists();

                if ($duplicateExists) {
                    $this->error("❌ Naming conflict: Cannot rename [{$role->name}] to [{$newRoleName}] because that name is taken.");

                    continue;
                }

                $this->line("🔄 Mapping candidate: [{$role->name}] ➡️ [{$newRoleName}]");

                if (! $isDryRun) {
                    DB::transaction(function () use ($role, $newRoleName): void {
                        $role->name = $newRoleName;
                        $role->save();
                    });
                    $anyChangesMade = true;
                }
            }
        }

        if ($anyChangesMade) {
            $this->newLine();
            $this->info('🧼 Flashing Spatie Permission cache backend...');
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $this->newLine();
        $this->info('🏁 Execution completely wrapped!');

        return Command::SUCCESS;
    }
}
