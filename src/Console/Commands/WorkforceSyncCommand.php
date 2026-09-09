<?php

declare(strict_types=1);

namespace Rimba\Sync\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Rimba\Sync\Actions\SyncWorkforceApiConfig;
use Rimba\Sync\Jobs\SyncWorkforceApiConfigJob;

#[Description('Fetch HRDB data and synchronize Staff and WFM assignments.')]
#[Signature('workforce:sync {apiConfig : ApiConfig ID} {--no-fetch} {--queue}')]
final class WorkforceSyncCommand extends Command
{
    public function handle(SyncWorkforceApiConfig $action): int
    {
        $id = (int) $this->argument('apiConfig');
        if (! $this->option('no-fetch')) {
            Artisan::call('rimba:fetch', ['identifier' => (string) $id], $this->output);
        }

        if ($this->option('queue')) {
            SyncWorkforceApiConfigJob::dispatch($id);
            $this->info('Workforce sync queued.');

            return self::SUCCESS;
        }

        $workforceSyncRun = $action->execute($id);
        $this->table(['Run', 'Status', 'Received', 'Processed', 'Created', 'Updated', 'Unchanged', 'Failed'], [[$workforceSyncRun->uuid, $workforceSyncRun->status->value, $workforceSyncRun->received, $workforceSyncRun->processed, $workforceSyncRun->created, $workforceSyncRun->updated, $workforceSyncRun->unchanged, $workforceSyncRun->failed]]);

        return $workforceSyncRun->failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
