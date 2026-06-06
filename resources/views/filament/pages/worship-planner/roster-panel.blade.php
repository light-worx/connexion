{{--
    filament/pages/worship-planner/roster-panel.blade.php
    Variables: $plan (WorshipPlan)

    Only shows rostergroups configured in worship_planner_roster settings
    for this service time. Falls back to showing all if no settings saved.
--}}
@php
    $rosterSettings   = setting('worship_planner_roster') ?? [];
    $timeSettings     = $rosterSettings[$plan->service_time] ?? [];
    $allowedGroupIds  = $timeSettings['rostergroup_ids'] ?? [];
    $configuredRoster = $timeSettings['roster_id'] ?? null;

    // Query rosteritems for this date + service time
    $date = $plan->sundayGroup->service_date->toDateString();

    $query = \App\Models\Rosteritem::whereHas('rosterGroup.roster', fn ($q) =>
            $q->where('sundayservice', $plan->service_time)
        )
        ->where('rosterdate', $date)
        ->with(['rosterGroup.group', 'individuals']);

    // Filter to only configured rostergroups if settings have been saved
    if (! empty($allowedGroupIds)) {
        $query->whereHas('rosterGroup', fn ($q) =>
            $q->whereIn('id', $allowedGroupIds)
        );
    }

    $rosterItems = $query->get();

    $rosterUrl = \Illuminate\Support\Facades\Route::has('filament.admin.people.resources.rosters.index')
        ? route('filament.admin.people.resources.rosters.index')
        : null;
@endphp

@if ($rosterItems->isNotEmpty())
    <div class="space-y-3">
        @foreach ($rosterItems as $rosterItem)
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    {{ $rosterItem->rosterGroup->group->name ?? '—' }}
                </p>
                @forelse ($rosterItem->individuals as $individual)
                    <div class="flex items-center gap-2 py-1">
                        <x-heroicon-s-user-circle class="w-5 h-5 text-gray-300 dark:text-gray-600 shrink-0" />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $individual->name
                                ?? trim(($individual->firstname ?? '') . ' ' . ($individual->lastname ?? ''))
                                ?: '—' }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">No one assigned yet</p>
                @endforelse
            </div>
        @endforeach
    </div>

    @if ($rosterUrl)
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ $rosterUrl }}" target="_blank"
                class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-800 font-medium">
                <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4" />
                Manage roster
            </a>
        </div>
    @endif

@else
    <div class="text-center py-10">
        <x-heroicon-o-user-group class="w-10 h-10 text-gray-300 mx-auto mb-3" />

        @if (empty($allowedGroupIds))
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">No roster groups configured</p>
            <p class="text-xs text-gray-400 max-w-xs mx-auto">
                Use the <strong>Roster Settings</strong> button in the page header to choose
                which rostergroups to display for the {{ $plan->service_time }} service.
            </p>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">No roster assignments found</p>
            <p class="text-xs text-gray-400 max-w-xs mx-auto">
                Assignments for the {{ $plan->service_time }} service on
                {{ $plan->sundayGroup->service_date->format('j M Y') }}
                will appear here once set in the roster.
            </p>
        @endif

        @if ($rosterUrl)
            <a href="{{ $rosterUrl }}" target="_blank"
                class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-800 font-medium mt-4">
                <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4" />
                Go to Roster
            </a>
        @endif
    </div>
@endif