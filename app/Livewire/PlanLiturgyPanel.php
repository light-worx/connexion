<?php

namespace App\Livewire;

use App\Models\WorshipPlanItem;
use App\Models\Prayer;
use Livewire\Component;
use Livewire\Attributes\Isolate;
use Illuminate\Support\Collection;

#[Isolate]
class PlanLiturgyPanel extends Component
{
    public int    $planId;
    public string $prayerSearch = '';

    public function getPrayerResultsProperty(): Collection
    {
        if (strlen($this->prayerSearch) < 2) return collect();

        return Prayer::where('title', 'like', "%{$this->prayerSearch}%")
            ->limit(10)
            ->get();
    }

    public function getPlanPrayersProperty(): Collection
    {
        return WorshipPlanItem::where('worship_plan_id', $this->planId)
            ->where('itemable_type', Prayer::class)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->with('itemable', 'suggestedBy')
            ->orderBy('position')
            ->orderBy('created_at')
            ->get();
    }

    public function addPrayer(int $prayerId): void
    {
        $exists = WorshipPlanItem::where('worship_plan_id', $this->planId)
            ->where('itemable_type', Prayer::class)
            ->where('itemable_id', $prayerId)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->exists();

        if ($exists) {
            $this->dispatch('notify', title: 'Prayer already added', type: 'warning');
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
    }

    public function confirmItem(int $itemId): void
    {
        $item   = WorshipPlanItem::findOrFail($itemId);
        $maxPos = WorshipPlanItem::where('worship_plan_id', $this->planId)
                    ->where('status', 'confirmed')->max('position') ?? 0;
        $item->confirm($maxPos + 1);
    }

    public function rejectItem(int $itemId): void
    {
        WorshipPlanItem::findOrFail($itemId)->reject();
    }

    public function removeItem(int $itemId): void
    {
        WorshipPlanItem::findOrFail($itemId)->delete();
    }

    public function render()
    {
        return view('livewire.plan-liturgy-panel');
    }
}