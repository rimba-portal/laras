<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_workforce_runs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('api_config_id')->nullable()->index();
            $table->string('source', 80)->index();
            $table->string('status', 30)->default('running')->index();
            $table->dateTime('started_at')->index();
            $table->dateTime('completed_at')->nullable()->index();
            $table->unsignedInteger('received')->default(0);
            $table->unsignedInteger('processed')->default(0);
            $table->unsignedInteger('created')->default(0);
            $table->unsignedInteger('updated')->default(0);
            $table->unsignedInteger('unchanged')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->json('summary')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
        Schema::create('sync_workforce_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sync_run_id')->constrained('sync_workforce_runs')->cascadeOnDelete();
            $table->string('source', 80)->index();
            $table->string('source_uuid', 191)->index();
            $table->char('checksum', 64)->index();
            $table->json('payload')->nullable();
            $table->dateTime('source_modified_at')->nullable()->index();
            $table->dateTime('captured_at')->index();
            $table->timestamps();
            $table->unique(['sync_run_id', 'source_uuid'], 'sync_workforce_snapshot_run_source_unique');
            $table->index(['source', 'source_uuid', 'captured_at'], 'sync_workforce_snapshot_lookup_idx');
        });
        Schema::create('sync_workforce_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sync_run_id')->constrained('sync_workforce_runs')->cascadeOnDelete();
            $table->foreignId('snapshot_id')->nullable()->constrained('sync_workforce_snapshots')->nullOnDelete();
            $table->string('source_uuid', 191)->index();
            $table->string('field', 100)->index();
            $table->json('before_value')->nullable();
            $table->json('after_value')->nullable();
            $table->string('classification', 80)->nullable()->index();
            $table->boolean('applied')->default(false)->index();
            $table->text('error')->nullable();
            $table->dateTime('detected_at')->index();
            $table->timestamps();
            $table->unique(['sync_run_id', 'source_uuid', 'field'], 'sync_workforce_change_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_workforce_changes');
        Schema::dropIfExists('sync_workforce_snapshots');
        Schema::dropIfExists('sync_workforce_runs');
    }
};
