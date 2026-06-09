<?php

namespace App\Livewire;

use App\Models\WorshipSundayGroup;
use Livewire\Component;
use Illuminate\Support\Collection;

/**
 * Shows a small roster-completeness indicator per time-slot on the planner card.
 * Green dot = all configured rostergroups have someone assigned.
 * Amber dot = some assigned but not all.
 * Gray dot  = none assigned yet.
 */
class WorshipPlanRosterPreview extends Component
{
    public int $groupId;

    /**
     * Returns completeness per service time.
     * Shape: [ '07h30' => ['filled' => 2, 'total' => 3], ... ]
     */
    public function getCompletionByTimeProperty(): Collection
    {
        $group = WorshipSundayGroup::with('plans')->find($this->groupId);

        if (! $group) return collect();

        $date            = $group->service_date->toDateString();
        $rosterSettings  = setting('worship_planner_roster') ?? [];

        return $group->plans->mapWithKeys(function ($plan) use ($date, $rosterSettings) {
            $time            = $plan->service_time;
            $timeSettings    = $rosterSettings[$time] ?? [];
            $allowedGroupIds = $timeSettings['rostergroup_ids'] ?? [];

            // If no rostergroups configured for this time, show nothing
            if (empty($allowedGroupIds)) {
                return [$time => null];
            }

            $total = count($allowedGroupIds);

            // Count how many configured rostergroups have at least one individual assigned
            $filled = \App\Models\Rosteritem::whereHas('rostergroup', fn ($q) =>
                    $q->whereIn('id', $allowedGroupIds)
                )
                ->where('rosterdate', $date)
                ->whereHas('individuals')
                ->count();

            return [$time => ['filled' => $filled, 'total' => $total]];
        })->filter(fn ($v) => $v !== null);
    }

    public function render()
    {
        return view('livewire.worship-plan-roster-preview');
    }
}