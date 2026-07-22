<?php

declare(strict_types=1);

namespace Rimba\Sync\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Rimba\Sync\Models\ApiConfig;
use Rimba\Sync\Services\FetchService;

#[Description('Fetch data using API pipeline configs')]
#[Signature('bites:fetch {identifier? : ID or name of the config}')]
class ApiFetchCommand extends Command
{
    public function handle(): int
    {
        $identifier = $this->argument('identifier');

        $configs = match (true) {
            is_numeric($identifier) => ApiConfig::where('id', $identifier)->get(),

            is_string($identifier) => ApiConfig::where('name', $identifier)->get(),

            default => ApiConfig::where('active', true)->get(),
        };

        if ($configs->isEmpty()) {
            $this->error('No API config found.');

            return self::FAILURE;
        }

        foreach ($configs as $config) {
            app(FetchService::class)->fetch($config);
        }

        $this->info('API fetch complete');

        return self::SUCCESS;
    }
}
