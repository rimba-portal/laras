<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_configs', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique()->index();
            $table->string('source_type');
            $table->json('source_config');
            $table->string('data_path')->nullable();
            $table->json('depends_on')->nullable();
            $table->json('mapping');
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('api_data', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('api_config_id')->constrained();
            $table->string('fingerprint')->nullable()->index();
            $table->json('payload');
            $table->string('status')->default('pending')->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_data');
        Schema::dropIfExists('api_configs');
    }
};
