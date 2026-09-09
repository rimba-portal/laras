<?php

declare(strict_types=1);

namespace Rimba\Sync\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'sync_workforce_changes')] #[Fillable(['sync_run_id', 'snapshot_id', 'source_uuid', 'field', 'before_value', 'after_value', 'classification', 'applied', 'error', 'detected_at'])]
class WorkforceChange extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['sync_run_id' => 'integer', 'snapshot_id' => 'integer', 'before_value' => 'json', 'after_value' => 'json', 'applied' => 'boolean', 'detected_at' => 'datetime'];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(WorkforceSyncRun::class, 'sync_run_id');
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(WorkforceSnapshot::class);
    }
}
