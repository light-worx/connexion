<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * worship_plan_items
     *
     * Staging table for songs and prayers being considered for a service.
     * This is the PLANNING layer — items here are proposals, not commitments.
     *
     * The two-phase model:
     *   Phase 1 (planning)  → rows live here with status: suggested / confirmed / rejected
     *   Phase 2 (finalised) → confirmed rows, ordered by `position`, are promoted
     *                          to service_items and worship_plan.status → published
     *
     * Polymorphic `itemable` allows both Song and Prayer models without separate
     * pivot tables for each.
     *
     * Typical itemable types:
     *   App\Models\Song
     *   App\Models\Prayer
     */
    public function up(): void
    {
        Schema::create('worship_plan_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('worship_plan_id')
                  ->constrained('worship_plans')
                  ->cascadeOnDelete();

            // ── Polymorphic item reference ─────────────────────────────────
            $table->morphs('itemable');   // adds itemable_type (string) + itemable_id (unsignedBigInt) + index

            // ── Planning metadata ──────────────────────────────────────────
            $table->enum('status', ['suggested', 'confirmed', 'rejected'])->default('suggested')
                  ->comment('"suggested" = under consideration. "confirmed" = included in final order. "rejected" = ruled out.');

            $table->unsignedSmallInteger('position')->nullable()
                  ->comment('Order within the service. Null while still in suggested state; set when confirming the final order.');

            $table->string('section')->nullable()
                  ->comment('Optional grouping label, e.g. "Opening", "Post-sermon", "Communion". Useful for long services.');

            $table->text('notes')->nullable()
                  ->comment('e.g. "Use acoustic version", "Transpose to Bb", "Leader: Jane".');

            // ── Suggested by ───────────────────────────────────────────────
            $table->foreignId('suggested_by_user_id')->nullable()
                  ->constrained('users')->nullOnDelete()
                  ->comment('Which staff member added this suggestion.');

            $table->timestamps();

            // ── Indexes ────────────────────────────────────────────────────
            $table->index(['worship_plan_id', 'status']);
            $table->index(['worship_plan_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worship_plan_items');
    }
};