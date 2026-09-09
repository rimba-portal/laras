<?php

declare(strict_types=1);

namespace Rimba\Sync\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rimba\Sync\Enums\WorkforceSyncRunStatus;

#[Table(name: 'sync_workforce_runs')] #[Fillable(['uuid', 'api_config_id', 'source', 'status', 'started_at', 'completed_at', 'received', 'processed', 'created', 'updated', 'unchanged', 'failed', 'summary', 'error'])]
class WorkforceSyncRun extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['api_config_id' => 'integer', 'status' => WorkforceSyncRunStatus::class, 'started_at' => 'datetime', 'completed_at' => 'datetime', 'received' => 'integer', 'processed' => 'integer', 'created' => 'integer', 'updated' => 'integer', 'unchanged' => 'integer', 'failed' => 'integer', 'summary' => 'array'];
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(WorkforceSnapshot::class, 'sync_run_id');
    }

    public function changes(): HasMany
    {
        return $this->hasMany(WorkforceChange::class, 'sync_run_id');
    }
}
