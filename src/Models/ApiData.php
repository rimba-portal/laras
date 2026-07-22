<?php

declare(strict_types=1);

namespace Rimba\Sync\Models;

use Rimba\Sync\Observers\ApiDataObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(ApiDataObserver::class)]
#[Fillable([
    'api_config_id',
    'fingerprint',
    'payload',
    'status',
    'processed_at',
    'error',
])]
class ApiData extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'api_config_id' => 'integer',
            'payload' => 'array',
            'processed_at' => 'timestamp',
        ];
    }

    public function apiConfig(): BelongsTo
    {
        return $this->belongsTo(ApiConfig::class);
    }

    public function markProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
            'error' => null,
            'payload' => 'committed',
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => 'failed',
            'error' => $message,
        ]);
    }
}
