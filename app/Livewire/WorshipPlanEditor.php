<?php

namespace App\Livewire;

use App\Models\WorshipPlan;
use App\Models\WorshipSundayGroup;
use App\Models\WorshipPlanItem;
use App\Models\Series;
use App\Models\Song;
use App\Models\Prayer;
use Livewire\Component;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class WorshipPlanEditor extends Component
{
    // ── Props ────────────────────────────────────────────────────────────────

    public int $planId;

    // ── State ────────────────────────────────────────────────────────────────

    public string $activeTab = 'details';  // details | songs | prayers | roster

    // Details tab fields
    public ?int    $seriesId         = null;
    public string  $bibleReading     = '';
    public bool    $overrideDefaults = false;
    public string  $overridePreacher = '';

    // Song/prayer search
    public string  $songSearch       = '';
    public string  $prayerSearch     = '';

    // ── Computed ─────────────────────────────────────────────────────────────

    public function getPlanProperty(): WorshipPlan
    {
        return WorshipPlan::with([
            'sundayGroup.series',
            'overrideSeries',
            'planItems.itemable',
            'planItems.suggestedBy',
        ])->findOrFail($this->planId);
    }

    public function getGroupProperty(): WorshipSundayGroup
    {
        return $this->plan->sundayGroup;
    }

    /**
     * Songs matching the search string.
     * Assumes songs table has: id, title, (optional) author
     * Adjust column names to match your schema.
     */
    public function getSongResultsProperty(): Collection
    {
        if (strlen($this->songSearch) < 2) {
            return collect();
        }

        return Song::where('title', 'like', "%{$this->songSearch}%")
            ->orWhere('author', 'like', "%{$this->songSearch}%")
            ->limit(10)
            ->get();
    }

    /**
     * Prayers matching the search string.
     * Assumes prayers table has: id, title, (optional) type/category
     * Adjust column names to match your schema.
     */
    public function getPrayerResultsProperty(): Collection
    {
        if (strlen($this->prayerSearch) < 2) {
            return collect();
        }

        return Prayer::where('title', 'like', "%{$this->prayerSearch}%")
            ->limit(10)
            ->get();
    }

    public function getPlanSongsProperty(): Collection
    {
        return $this->plan->planItems()
            ->where('itemable_type', Song::class)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->with('itemable')
            ->orderBy('position')
            ->orderBy('created_at')
            ->get();
    }

    public function getPlanPrayersProperty(): Collection
    {
        return $this->plan->planItems()
            ->where('itemable_type', Prayer::class)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->with('itemable')
            ->orderBy('position')
            ->orderBy('created_at')
            ->get();
    }

    public function getRosterItemsProperty(): Collection
    {
        return $this->plan->rosterItems();
    }

    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $plan = $this->plan;

        // Populate form fields from current plan state
        $this->overrideDefaults = ! $plan->uses_group_defaults;
        $this->seriesId         = $plan->override_series_id
                                    ?? $plan->sundayGroup->series_id;
        $this->bibleReading     = $plan->override_bible_reading
                                    ?? $plan->sundayGroup->bible_reading
                                    ?? '';
        $this->overridePreacher = $plan->override_preacher_name ?? '';
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    public function saveDetails(): void
    {
        $plan  = $this->plan;
        $group = $this->group;

        if ($this->overrideDefaults) {
            // Save overrides directly on this plan's time-slot
            $plan->update([
                'override_series_id'     => $this->seriesId,
                'override_bible_reading' => $this->bibleReading ?: null,
                'override_preacher_name' => $this->overridePreacher ?: null,
            ]);
        } else {
            // Save as group-level defaults (shared across all time-slots)
            $group->update([
                'series_id'    => $this->seriesId,
                'bible_reading'=> $this->bibleReading ?: null,
            ]);

            // Clear any overrides on this plan
            $plan->update([
                'override_series_id'     => null,
                'override_bible_reading' => null,
                'override_preacher_name' => null,
            ]);
        }

        Notification::make()
            ->title('Details saved')
            ->success()
            ->send();
    }

    public function addSong(int $songId): void
    {
        // Prevent duplicates
        $exists = WorshipPlanItem::where('worship_plan_id', $this->planId)
            ->where('itemable_type', Song::class)
            ->where('itemable_id', $songId)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Song already added')
                ->warning()
                ->send();
            return;
        }

        WorshipPlanItem::create([
            'worship_plan_id'      => $this->planId,
            'itemable_type'        => Song::class,
            'itemable_id'          => $songId,
            'status'               => 'suggested',
            'suggested_by_user_id' => auth()->id(),
        ]);

        $this->songSearch = '';

        Notification::make()
            ->title('Song added')
            ->success()
            ->send();
    }

    public function addPrayer(int $prayerId): void
    {
        $exists = WorshipPlanItem::where('worship_plan_id', $this->planId)
            ->where('itemable_type', Prayer::class)
            ->where('itemable_id', $prayerId)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Prayer already added')
                ->warning()
                ->send();
            return;
        }

        WorshipPlanItem::create([
            'worship_plan_id'      => $this->planId,
            'itemable_type'        => Prayer::class,
            'itemable_id'          => $prayerId,
            'status'               => 'suggested',
            'suggested_by_user_id' => auth()->id(),
        ]);

        $this->prayerSearch = '';

        Notification::make()
            ->title('Prayer added')
            ->success()
            ->send();
    }

    public function removeItem(int $itemId): void
    {
        WorshipPlanItem::findOrFail($itemId)->delete();
    }

    public function confirmItem(int $itemId): void
    {
        $item     = WorshipPlanItem::findOrFail($itemId);
        $maxPos   = WorshipPlanItem::where('worship_plan_id', $this->planId)
                        ->where('status', 'confirmed')
                        ->max('position') ?? 0;

        $item->confirm($maxPos + 1);
    }

    public function rejectItem(int $itemId): void
    {
        WorshipPlanItem::findOrFail($itemId)->reject();
    }

    public function close(): void
    {
        $this->dispatch('close-plan-editor');
    }

    // ── View ─────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.worship-plan-editor');
    }
}