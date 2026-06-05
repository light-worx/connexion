<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class WorshipSundayGroup extends Model
{
    protected $fillable = [
        'service_date',
        'display_name',
        'is_special_service',
        'preacher_name',
        'preacher_api_id',
        'series_id',
        'bible_reading',
        'api_synced_at',
        'api_raw',
        'notes',
    ];

    protected $casts = [
        'service_date'      => 'date',
        'is_special_service'=> 'boolean',
        'api_synced_at'     => 'datetime',
        'api_raw'           => 'array',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function plans(): HasMany
    {
        return $this->hasMany(WorshipPlan::class)
                    ->orderBy('service_time');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    /**
     * Human-readable label for the date.
     * Returns display_name if set (e.g. "Good Friday"), otherwise formats
     * the date as "4 Jan" / "25 Dec" etc.
     */
    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->display_name
                ?? $this->service_date->format('j M'),
        );
    }

    /**
     * The month name, used for grouping in the planner view.
     */
    protected function monthName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->service_date->format('F'),
        );
    }

    /**
     * The month number (1–12), used for sorting groups.
     */
    protected function monthNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->service_date->format('n'),
        );
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('service_date', $year)
                     ->orderBy('service_date');
    }

    public function scopeSpecialServices($query)
    {
        return $query->where('is_special_service', true);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * True if all child plans have been published.
     */
    public function isFullyPublished(): bool
    {
        return $this->plans->isNotEmpty()
            && $this->plans->every(fn ($p) => $p->status === 'published');
    }

    /**
     * Aggregate status across all time-slot plans for display on the card.
     * Returns: 'published' | 'confirmed' | 'draft'
     */
    public function aggregateStatus(): string
    {
        $statuses = $this->plans->pluck('status');

        if ($statuses->isEmpty() || $statuses->contains('draft')) {
            return 'draft';
        }
        if ($statuses->contains('confirmed')) {
            return 'confirmed';
        }
        return 'published';
    }
}