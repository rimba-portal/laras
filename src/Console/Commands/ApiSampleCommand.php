<?php

declare(strict_types=1);

namespace Rimba\Sync\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Rimba\Sync\Models\ApiConfig;
use Symfony\Component\Yaml\Yaml;

#[Description('Sample top 10 raw rows from API config (JSON/YAML)')]
#[Signature('rimba:sample 
                            {identifier? : ID or name of the config}
                            {--json : Output as JSON}
                            {--yaml : Output as YAML}')]
class ApiSampleCommand extends Command
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
            $this->info(sprintf('=== Sampling: %s (ID: %s) ===', $config->name, $config->id));

            try {
                $rows = match ($config->source_type) {
                    'rest' => $this->handleRest($config),
                    'database' => $this->handleDatabase($config),
                    default => throw new \Exception('Unsupported source type: '.$config->source_type),
                };

                if ($rows === []) {
                    $this->warn('No data returned.');

                    continue;
                }

                // ✅ Output modes
                if ($this->option('yaml')) {
                    $this->line(Yaml::dump($rows, 4, 2));
                } else {
                    // default = JSON
                    $this->line(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }

            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        $this->info('API sample complete');

        return self::SUCCESS;
    }

    protected function handleRest(ApiConfig $config): array
    {
        $cfg = $config->source_config;

        $response = Http::withHeaders($cfg['headers'] ?? [])
            ->get($cfg['url']);

        if (! $response->successful()) {
            throw new \Exception('HTTP error: '.$response->status());
        }

        $data = $response->json();

        if ($config->data_path) {
            $data = Arr::get($data, $config->data_path);
        }

        return collect($data)->take(10)->values()->toArray();
    }

    protected function handleDatabase(ApiConfig $config): array
    {
        $cfg = $config->source_config;

        $rows = DB::connection($cfg['connection'] ?? null)
            ->select($cfg['query']);

        return collect($rows)
            ->take(10)
            ->map(fn ($row): array => (array) $row)
            ->toArray();
    }
}
