<?php

declare(strict_types=1);

namespace Rimba\Sync\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'sync_workforce_snapshots')] #[Fillable(['sync_run_id', 'source', 'source_uuid', 'checksum', 'payload', 'source_modified_at', 'captured_at'])]
class WorkforceSnapshot extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['sync_run_id' => 'integer', 'payload' => 'array', 'source_modified_at' => 'datetime', 'captured_at' => 'datetime'];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(WorkforceSyncRun::class, 'sync_run_id');
    }
}
