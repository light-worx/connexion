<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * worship_sunday_groups
     *
     * Represents a single calendar date on which one or more services are held.
     * Stores the "shared defaults" for all time slots on that date (preacher,
     * series, Bible reading). Individual worship_plans rows for each time slot
     * may override these via their own nullable columns.
     *
     * Special / midweek services (Christmas, Good Friday, etc.) are also stored
     * here — distinguished by is_special_service = true and a descriptive name.
     */
    public function up(): void
    {
        Schema::create('worship_sunday_groups', function (Blueprint $table) {
            $table->id();

            // ── Date & identity ──────────────────────────────────────────────
            $table->date('service_date')->unique();
            $table->string('display_name')->nullable()
                  ->comment('Human-readable label, e.g. "Good Friday" or "Christmas Day". Null for regular Sundays.');
            $table->boolean('is_special_service')->default(false)
                  ->comment('True for midweek / non-Sunday services sourced from the external API.');

            // ── Shared defaults (sourced from the external API, cached locally) ─
            $table->string('preacher_name')->nullable()
                  ->comment('Cached from external API. Overridable per time-slot in worship_plans.');
            $table->string('preacher_api_id')->nullable()
                  ->comment('The external API identifier for the preacher, for re-syncing.');

            $table->foreignId('series_id')->nullable()->constrained('series')->nullOnDelete()
                  ->comment('Sermon series assigned to this date. Overridable per time-slot.');
            $table->string('bible_reading')->nullable()
                  ->comment('Primary Bible passage, e.g. "Romans 8:1-17". Overridable per time-slot.');

            // ── API sync metadata ─────────────────────────────────────────────
            $table->timestamp('api_synced_at')->nullable()
                  ->comment('When the external API data was last fetched for this date.');
            $table->json('api_raw')->nullable()
                  ->comment('Raw API payload snapshot for debugging / re-processing.');

            // ── Notes ─────────────────────────────────────────────────────────
            $table->text('notes')->nullable();

            $table->timestamps();

            // ── Indexes ───────────────────────────────────────────────────────
            $table->index('service_date');
            $table->index('series_id');
            $table->index('is_special_service');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worship_sunday_groups');
    }
};