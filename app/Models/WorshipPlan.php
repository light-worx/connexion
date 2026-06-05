<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;

class WorshipPlan extends Model
{
    protected $fillable = [
        'worship_sunday_group_id',
        'service_time',
        'override_preacher_name',
        'override_preacher_api_id',
        'override_series_id',
        'override_bible_reading',
        'status',
        'published_at',
        'notes',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function sundayGroup(): BelongsTo
    {
        return $this->belongsTo(WorshipSundayGroup::class, 'worship_sunday_group_id');
    }

    public function overrideSeries(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'override_series_id');
    }

    public function planItems(): HasMany
    {
        return $this->hasMany(WorshipPlanItem::class)
                    ->orderBy('position')
                    ->orderBy('created_at');
    }

    public function suggestedItems(): HasMany
    {
        return $this->planItems()->where('status', 'suggested');
    }

    public function confirmedItems(): HasMany
    {
        return $this->planItems()->where('status', 'confirmed')->orderBy('position');
    }

    // ── "Inherit or override" accessors ─────────────────────────────────────

    protected function effectivePreacher(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->override_preacher_name
                ?? $this->sundayGroup?->preacher_name,
        );
    }

    protected function effectiveSeries(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->overrideSeries
                ?? $this->sundayGroup?->series,
        );
    }

    protected function effectiveBibleReading(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->override_bible_reading
                ?? $this->sundayGroup?->bible_reading,
        );
    }

    protected function usesGroupDefaults(): Attribute
    {
        return Attribute::make(
            get: fn () => is_null($this->override_preacher_name)
                && is_null($this->override_series_id)
                && is_null($this->override_bible_reading),
        );
    }

    // ── Roster integration ───────────────────────────────────────────────────

    /**
     * Returns all rosteritems for this plan's date and matching service time,
     * with their groups and assigned individuals loaded.
     * Respects group_individual.categories filtering.
     */
    public function rosterItems(): Collection
    {
        $date        = $this->sundayGroup->service_date->toDateString();
        $serviceTime = $this->service_time; // e.g. "07h30"

        return RosterItem::whereHas('rosterGroup.roster', function ($q) use ($serviceTime) {
                $q->where('sundayservice', $serviceTime);
            })
            ->where('rosterdate', $date)
            ->with([
                'rosterGroup.group',
                'individuals' => function ($q) use ($serviceTime) {
                    $q->whereHas('groupIndividuals', function ($q2) use ($serviceTime) {
                        $q2->where(function ($q3) use ($serviceTime) {
                            $q3->whereNull('categories')
                               ->orWhereJsonContains('categories', $serviceTime);
                        });
                    });
                },
            ])
            ->get();
    }

    // ── Status helpers ───────────────────────────────────────────────────────

    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isConfirmed(): bool { return $this->status === 'confirmed'; }
    public function isPublished(): bool { return $this->status === 'published'; }

    /**
     * Promote confirmed plan items to service_items and mark as published.
     */
    public function publish(): void
    {
        \DB::transaction(function () {
            $this->confirmedItems
                ->each(function (WorshipPlanItem $item, int $index) {
                    ServiceItem::create([
                        'service_id'    => $this->getOrCreateServiceId(),
                        'itemable_type' => $item->itemable_type,
                        'itemable_id'   => $item->itemable_id,
                        'position'      => $item->position ?? $index + 1,
                        'section'       => $item->section,
                        'notes'         => $item->notes,
                    ]);
                });

            $this->update([
                'status'       => 'published',
                'published_at' => now(),
            ]);
        });
    }

    /**
     * Find or create the corresponding Service record for this plan.
     * Adjust to match how your services table is keyed.
     */
    private function getOrCreateServiceId(): int
    {
        $service = \App\Models\Service::firstOrCreate([
            'service_date' => $this->sundayGroup->service_date,
            'service_time' => $this->service_time,
        ]);

        return $service->id;
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeDraft($query)     { return $query->where('status', 'draft'); }
    public function scopePublished($query) { return $query->where('status', 'published'); }
}