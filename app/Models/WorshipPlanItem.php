<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorshipPlanItem extends Model
{
    protected $fillable = [
        'worship_plan_id',
        'itemable_type',
        'itemable_id',
        'status',
        'position',
        'section',
        'notes',
        'suggested_by_user_id',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function worshipPlan(): BelongsTo
    {
        return $this->belongsTo(WorshipPlan::class);
    }

    /**
     * Polymorphic — resolves to either Song or Prayer model.
     */
    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function suggestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_by_user_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeSuggested($query)  { return $query->where('status', 'suggested'); }
    public function scopeConfirmed($query)  { return $query->where('status', 'confirmed'); }
    public function scopeRejected($query)   { return $query->where('status', 'rejected'); }
    public function scopeSongs($query)      { return $query->where('itemable_type', Song::class); }
    public function scopePrayers($query)    { return $query->where('itemable_type', Prayer::class); }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isSong(): bool   { return $this->itemable_type === Song::class; }
    public function isPrayer(): bool { return $this->itemable_type === Prayer::class; }

    public function confirm(int $position): void
    {
        $this->update(['status' => 'confirmed', 'position' => $position]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected', 'position' => null]);
    }
}