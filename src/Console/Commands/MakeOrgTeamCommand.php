<?php

declare(strict_types=1);

namespace Rimba\Sync\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Rimba\Organization\Models\OrgTeam;
use Rimba\People\Models\Staff;
use Spatie\Permission\Models\Role;

#[Description('Step 1: Parse staff◇team◇ roles by Staff UUID and upsert corresponding OrgTeam records matching attributes->uuid')]
#[Signature('rimba:leaders-team {--dry-run : Simulate team creations without saving}')]
class MakeOrgTeamCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $prefix = 'staff◇team◇';
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🧪 DRY RUN MODE ACTIVE - No OrgTeam records will be written.');
        }

        // Find all roles matching the staff team prefix
        $roles = Role::where('name', 'like', "{$prefix}%")->get();

        if ($roles->isEmpty()) {
            $this->warn("No roles found matching target prefix: {$prefix}");

            return Command::SUCCESS;
        }

        $this->info("Processing {$roles->count()} roles to seed OrgTeam database records...");

        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach ($roles as $role) {
            $uuid = Str::after($role->name, $prefix);

            // if (!Str::isUuid($uuid)) {
            //     $this->warn("⚠️ Skipping role [{$role->name}]: Trailing string is not a valid UUID.");
            //     $failed++;
            //     continue;
            // }

            // Look up the legacy link via Staff table
            $staff = Staff::where('uuid', $uuid)->first();

            if (! $staff) {
                $this->error("❌ Legacy Staff row not found for UUID: {$uuid} (Role: {$role->name})");
                $failed++;

                continue;
            }

            // Prepare team payload values based on staff data
            $teamName = 'Team - '.($staff->name ?: 'Staff '.$staff->staff_no);
            $teamCode = $staff->staff_no ? "{$staff->staff_no}'s team" : null;

            $this->line("📦 Preparing OrgTeam for UUID [{$uuid}]: Name: '{$teamName}', Code: '{$teamCode}', OrgUnit: {$staff->org_unit_id}");

            if (! $isDryRun) {
                // Find existing team by matching the uuid inside the attributes JSON column
                $existingTeam = OrgTeam::where('attributes->uuid', $uuid)->first();

                if ($existingTeam) {
                    // Update existing record and preserve current attributes while updating/adding the uuid
                    $currentAttributes = $existingTeam->attributes ?? [];
                    $currentAttributes['uuid'] = $uuid;

                    $existingTeam->update([
                        'org_unit_id' => $staff->org_unit_id,
                        'name' => $teamName,
                        'code' => $teamCode,
                        'is_active' => true,
                        'attributes' => $currentAttributes,
                    ]);
                    $updated++;
                } else {
                    // Create a completely new record
                    OrgTeam::create([
                        'org_unit_id' => $staff->org_unit_id,
                        'name' => $teamName,
                        'code' => $teamCode,
                        'is_active' => true,
                        'attributes' => ['uuid' => $uuid],
                    ]);
                    $created++;
                }
            } else {
                $created++;
            }
        }

        $this->newLine();
        $this->info("🏁 Step 1 Complete! Teams Created: {$created}, Updated: {$updated}, Errors/Skipped: {$failed}");

        return Command::SUCCESS;
    }
}
