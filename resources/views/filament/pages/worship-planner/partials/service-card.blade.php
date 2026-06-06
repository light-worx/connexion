{{--
    Partial: filament/pages/worship-planner/partials/service-card.blade.php
    Variables: $group (WorshipSundayGroup with plans, series loaded)
--}}
@php
    $status      = $group->aggregateStatus();
    $series      = $group->series;
    $plans       = $group->plans;
    $isSpecial   = $group->is_special_service;

    $statusColour = match ($status) {
        'published' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300',
        'confirmed' => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300',
        default     => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
    };
@endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 p-3 flex flex-col gap-2 hover:border-primary-400 transition group">

    {{-- Date + status --}}
    <div class="flex items-start justify-between gap-2">
        <div>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $group->label }}
                @if ($isSpecial)
                    <span class="ml-1 text-xs text-primary-500">★</span>
                @endif
            </span>
            @if ($group->preacher_name)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $group->preacher_name }}</p>
            @endif
        </div>
        <span class="shrink-0 text-[10px] font-medium px-2 py-0.5 rounded-full {{ $statusColour }}">
            {{ ucfirst($status) }}
        </span>
    </div>

    {{-- Series / reading --}}
    @if ($series || $group->bible_reading)
        <div class="text-xs text-gray-600 dark:text-gray-300 space-y-0.5">
            @if ($series)
                <p class="font-medium text-primary-600 dark:text-primary-400">{{ $series->name }}</p>
            @endif
            @if ($group->bible_reading)
                <p>{{ $group->bible_reading }}</p>
            @endif
        </div>
    @else
        <p class="text-xs italic text-gray-400 dark:text-gray-500">+ Assign series</p>
    @endif

    {{-- Per-time-slot rows — entire row is clickable --}}
    @foreach ($plans as $plan)
        @php
            $songCount   = $plan->planItems->where('itemable_type', \App\Models\Song::class)->count();
            $prayerCount = $plan->planItems->where('itemable_type', \App\Models\Prayer::class)->count();
        @endphp

        <button
            wire:click="editPlan({{ $plan->id }})"
            class="w-full flex items-center justify-between rounded-lg bg-white dark:bg-gray-800 px-2 py-1.5 border border-gray-100 dark:border-gray-700 hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition text-left"
        >
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-semibold text-gray-700 dark:text-gray-300">
                    {{ $plan->service_time }}
                </span>
                @if ($songCount > 0)
                    <span class="flex items-center gap-0.5 text-[11px] text-gray-500">
                        <x-heroicon-s-musical-note class="w-3 h-3" /> {{ $songCount }}
                    </span>
                @endif
                @if ($prayerCount > 0)
                    <span class="flex items-center gap-0.5 text-[11px] text-gray-500">
                        <x-heroicon-s-hand-raised class="w-3 h-3" /> {{ $prayerCount }}
                    </span>
                @endif
                @if (! $plan->uses_group_defaults)
                    <span class="text-[10px] text-amber-500" title="Has individual overrides">⚡</span>
                @endif
            </div>
            <x-heroicon-s-pencil-square class="w-3.5 h-3.5 text-gray-300 group-hover:text-primary-400 transition" />
        </button>
    @endforeach

    {{-- Roster preview --}}
    <div class="mt-0.5">
        @livewire('worship-plan-roster-preview', ['groupId' => $group->id], key('roster-' . $group->id))
    </div>
</div>