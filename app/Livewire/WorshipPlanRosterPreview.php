<?php

namespace App\Livewire;

use App\Models\WorshipSundayGroup;
use App\Models\Rosteritem;
use Livewire\Component;
use Illuminate\Support\Collection;

/**
 * Lightweight read-only component that shows who is rostered for each
 * time-slot on a given Sunday, rendered inside each planner card.
 *
 * Loaded lazily to avoid N+1 queries on the main planner view.
 */
class WorshipPlanRosterPreview extends Component
{
    public int $groupId;

    /**
     * Returns roster assignments grouped by service_time.
     * Shape: [ '07h30' => [ ['group' => 'Pianists', 'individuals' => [...]], ... ], ... ]
     */
    public function getRosterByTimeProperty(): Collection
    {
        $group = WorshipSundayGroup::find($this->groupId);

        if (! $group) {
            return collect();
        }

        $date = $group->service_date->toDateString();

        // Get service times for this group from its plans
        $serviceTimes = $group->plans->pluck('service_time');

        return $serviceTimes->mapWithKeys(function (string $time) use ($date) {
            $items = Rosteritem::whereHas('rostergroup.roster', fn ($q) =>
                    $q->where('sundayservice', $time)
                )
                ->where('rosterdate', $date)
                ->with([
                    'rosterGroup.group',
                    'individuals',
                ])
                ->get();
                dd($items);
                /*->map(fn ($item) => [
                    'group_name'  => $item->rosterGroup->group->name ?? '—',
                    'individuals' => $item->individuals->pluck('name')->join(', '),
                ]);*/

            return [$time => $items];
        })->filter(fn ($items) => $items->isNotEmpty());
    }

    public function render()
    {
        return view('livewire.worship-plan-roster-preview');
    }
}