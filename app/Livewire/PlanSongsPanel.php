<?php

namespace App\Livewire;

use App\Models\WorshipPlan;
use App\Models\WorshipPlanItem;
use App\Models\Song;
use Livewire\Component;
use Livewire\Attributes\Isolate;
use Illuminate\Support\Collection;

#[Isolate]
class PlanSongsPanel extends Component
{
    public int    $planId;
    public string $songSearch = '';

    public function getSongResultsProperty(): Collection
    {
        if (strlen($this->songSearch) < 2) return collect();

        return Song::where('title', 'like', "%{$this->songSearch}%")
            ->orWhere('author', 'like', "%{$this->songSearch}%")
            ->limit(10)
            ->get();
    }

    public function getPlanSongsProperty(): Collection
    {
        return WorshipPlanItem::where('worship_plan_id', $this->planId)
            ->where('itemable_type', Song::class)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->with('itemable', 'suggestedBy')
            ->orderBy('position')
            ->orderBy('created_at')
            ->get();
    }

    public function addSong(int $songId): void
    {
        $exists = WorshipPlanItem::where('worship_plan_id', $this->planId)
            ->where('itemable_type', Song::class)
            ->where('itemable_id', $songId)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->exists();

        if ($exists) {
            $this->dispatch('notify', title: 'Song already added', type: 'warning');
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
        return view('livewire.plan-songs-panel');
    }
}