{{--
    Partial: filament/pages/worship-planner/partials/service-card.blade.php
    Variables: $group (WorshipSundayGroup with plans, series loaded)
--}}
@php
    $status    = $group->aggregateStatus();
    $series    = $group->series;
    $plans     = $group->plans;
    $isSpecial = $group->is_special_service;

    $statusColour = match ($status) {
        'published' => 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300',
        'confirmed' => 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300',
        default     => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
    };
@endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 p-3 flex flex-col gap-2 hover:border-primary-400 transition group">

    {{-- Date + aggregate status --}}
    <div class="flex items-start justify-between gap-2">
        <div>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $group->label }}
                @if ($isSpecial)
                    <span class="ml-1 text-xs text-primary-500">★</span>
                @endif
            </span>
            @if ($group->preacher_name)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $group->preacher_name }}
                </p>
            @endif
        </div>
        <span class="shrink-0 text-[10px] font-medium px-2 py-0.5 rounded-full {{ $statusColour }}">
            {{ ucfirst($status) }}
        </span>
    </div>

    {{-- Series image + name + bible reading --}}
    @if ($series || $group->bible_reading)
        <div class="flex items-center gap-2">
            @if ($series && isset($series->image) && $series->image)
                <img
                    src="{{ Storage::url($series->image) }}"
                    alt="{{ $series->series }}"
                    class="h-10 w-auto rounded-lg object-contain shrink-0 border border-gray-200 dark:border-gray-700"
                />
            @endif
            <div class="text-xs text-gray-600 dark:text-gray-300 space-y-0.5 min-w-0">
                @if ($series)
                    <p class="font-medium text-primary-600 dark:text-primary-400 truncate">
                        {{ $series->series }}
                    </p>
                @endif
                @if ($group->bible_reading)
                    <p class="truncate">{{ $group->bible_reading }}</p>
                @endif
            </div>
        </div>
    @else
        <p class="text-xs italic text-gray-400 dark:text-gray-500">+ Assign series</p>
    @endif

    {{-- Per-time-slot rows --}}
    @foreach ($plans as $plan)
        @php
            $songCount    = $plan->planItems->where('itemable_type', \App\Models\Song::class)->count();
            $prayerCount  = $plan->planItems->where('itemable_type', \App\Models\Prayer::class)->count();
            $isPublished  = $plan->status === 'published';
        @endphp

        <div class="rounded-lg border bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700
                    hover:border-primary-300 dark:hover:border-primary-700 transition
                    @if($isPublished) opacity-75 @endif">

            {{-- Main clickable row — opens edit modal --}}
            <button
                wire:click="editPlan({{ $plan->id }})"
                class="w-full flex items-center justify-between px-2 py-1.5 text-left"
            >
                <div class="flex items-center gap-1.5 flex-wrap">
                    {{-- Time --}}
                    <span class="text-xs font-mono font-semibold text-gray-700 dark:text-gray-300">
                        {{ $plan->service_time }}
                    </span>

                    {{-- Service type badge e.g. COM --}}
                    @if ($plan->service_type)
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded
                                     bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300">
                            {{ $plan->service_type }}
                        </span>
                    @endif

                    {{-- Published badge --}}
                    @if ($isPublished)
                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded
                                     bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300">
                            ✓ Published
                        </span>
                    @endif

                    {{-- Item counts --}}
                    @if ($songCount > 0)
                        <span class="flex items-center gap-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                            <x-heroicon-s-musical-note class="w-3 h-3" /> {{ $songCount }}
                        </span>
                    @endif
                    @if ($prayerCount > 0)
                        <span class="flex items-center gap-0.5 text-[11px] text-gray-500 dark:text-gray-400">
                            <x-heroicon-s-hand-raised class="w-3 h-3" /> {{ $prayerCount }}
                        </span>
                    @endif
                    @if (! $plan->uses_group_defaults)
                        <span class="text-[10px] text-amber-500" title="Has individual overrides">⚡</span>
                    @endif
                </div>

                <x-heroicon-s-pencil-square
                    class="w-3.5 h-3.5 text-gray-300 group-hover:text-primary-400 shrink-0 transition ml-1"
                />
            </button>

            {{-- Finalise button — only shown when not yet published --}}
            @if (! $isPublished)
                <div class="border-t border-gray-100 dark:border-gray-700 px-2 py-1">
                    <button
                        wire:click="finalisePlanDirect({{ $plan->id }})"
                        wire:confirm="Finalise the {{ $plan->service_time }} service on {{ $group->service_date->format('j M Y') }}? This will publish the order of service."
                        class="w-full flex items-center justify-center gap-1.5 text-[11px] font-medium
                               text-success-600 hover:text-success-800
                               dark:text-success-400 dark:hover:text-success-200
                               hover:bg-success-50 dark:hover:bg-success-900/20
                               rounded py-0.5 transition"
                    >
                        <x-heroicon-s-check-badge class="w-3.5 h-3.5" />
                        Finalise
                    </button>
                </div>
            @endif
        </div>
    @endforeach

    {{-- Roster preview --}}
    <div class="mt-0.5">
        @livewire('worship-plan-roster-preview', ['groupId' => $group->id], key('roster-' . $group->id))
    </div>
</div>