<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * worship_plans
     *
     * One row per service time-slot on a given date.
     * e.g. 4 Jan 2026 at 07:30, 4 Jan 2026 at 09:00, 4 Jan 2026 at 18:30.
     *
     * The "inherit & override" pattern:
     *   - By default a time-slot inherits preacher / series / bible_reading
     *     from its parent worship_sunday_group.
     *   - When a service genuinely differs (different preacher, own series, etc.)
     *     the override_* columns are populated and take precedence.
     *
     * Status lifecycle:
     *   draft → confirmed → published
     *   "published" means the order of service has been finalised and
     *   worship_plan_items have been promoted to service_items.
     */
    public function up(): void
    {
        Schema::create('worship_plans', function (Blueprint $table) {
            $table->id();

            // ── Parent group ──────────────────────────────────────────────────
            $table->foreignId('worship_sunday_group_id')
                  ->constrained('worship_sunday_groups')
                  ->cascadeOnDelete();

            // ── Time slot ─────────────────────────────────────────────────────
            $table->string('service_time');

            // ── Override columns (null = inherit from group) ──────────────────
            $table->string('override_preacher_name')->nullable()
                  ->comment('Set only when this slot has a different preacher than the group default.');
            $table->string('override_preacher_api_id')->nullable();

            $table->unsignedInteger('override_series_id')->nullable();
            $table->foreign('override_series_id')->references('id')->on('series')->nullOnDelete();

            $table->string('override_bible_reading')->nullable()
                  ->comment('Set only when this slot uses a different passage than the group default.');

            // ── Status ────────────────────────────────────────────────────────
            $table->enum('status', ['draft', 'confirmed', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable()
                  ->comment('When status was set to published and service_items were populated.');

            // ── Notes ─────────────────────────────────────────────────────────
            $table->text('notes')->nullable();

            $table->timestamps();

            // ── Constraints ───────────────────────────────────────────────────
            // Each group can only have one plan per time slot.
            $table->unique(['worship_sunday_group_id', 'service_time']);

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index('status');
            $table->index('override_series_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worship_plans');
    }
};