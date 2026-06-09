{{--
    Partial: filament/pages/worship-planner/partials/service-card.blade.php
    Variables: $group (WorshipSundayGroup with plans, series loaded)
--}}
@php
    $series    = $group->series;
    $plans     = $group->plans;
    $isSpecial = $group->is_special_service;
@endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 p-3 flex flex-col gap-2 hover:border-primary-400 transition group">

    {{-- Date row — no aggregate status pill --}}
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

    {{-- Per-time-slot rows — colour coded by status, no text pill --}}
    @foreach ($plans as $plan)
        @php
            $songCount   = $plan->planItems->where('itemable_type', \App\Models\Song::class)->count();
            $prayerCount = $plan->planItems->where('itemable_type', \App\Models\Prayer::class)->count();

            $rowClass = match ($plan->status) {
                'published' => 'border-success-200 bg-success-50 dark:border-success-800 dark:bg-success-900/30',
                'confirmed' => 'border-warning-200 bg-warning-50 dark:border-warning-800 dark:bg-warning-900/30',
                default     => 'border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800',
            };

            $timeClass = match ($plan->status) {
                'published' => 'text-success-700 dark:text-success-400',
                'confirmed' => 'text-warning-700 dark:text-warning-400',
                default     => 'text-gray-700 dark:text-gray-300',
            };

            // Roster completeness dot
            $rosterSettings  = setting('worship_planner_roster') ?? [];
            $timeSettings    = $rosterSettings[$plan->service_time] ?? [];
            $allowedGroupIds = $timeSettings['rostergroup_ids'] ?? [];
            $rosterTotal     = count($allowedGroupIds);

            if ($rosterTotal > 0) {
                $rosterFilled = \App\Models\Rosteritem::whereHas('rostergroup', fn ($q) =>
                        $q->whereIn('id', $allowedGroupIds)
                    )
                    ->where('rosterdate', $group->service_date->toDateString())
                    ->whereHas('individuals')
                    ->count();

                $rosterDot = match(true) {
                    $rosterFilled === 0          => ['colour' => 'bg-gray-300 dark:bg-gray-600',      'tip' => "No roster assigned ({$rosterTotal} needed)"],
                    $rosterFilled < $rosterTotal => ['colour' => 'bg-amber-400 dark:bg-amber-500',    'tip' => "Partially rostered ({$rosterFilled}/{$rosterTotal})"],
                    default                      => ['colour' => 'bg-success-400 dark:bg-success-500','tip' => "Fully rostered ({$rosterFilled}/{$rosterTotal})"],
                };
            } else {
                $rosterDot = null;
            }
        @endphp

        <button
            wire:click="editPlan({{ $plan->id }})"
            class="w-full flex items-center justify-between rounded-lg border px-2 py-1.5
                   hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20
                   transition text-left {{ $rowClass }}"
        >
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-xs font-mono font-semibold {{ $timeClass }}">
                    {{ $plan->service_time }}
                </span>

                {{-- Service type badge e.g. COM --}}
                @if ($plan->service_type)
                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded
                                 bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300">
                        {{ $plan->service_type }}
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

            <div class="flex items-center gap-1.5 shrink-0">
                @if ($rosterDot)
                    <span
                        class="w-2 h-2 rounded-full {{ $rosterDot['colour'] }}"
                        title="{{ $rosterDot['tip'] }}"
                    ></span>
                @endif
                <x-heroicon-s-pencil-square
                    class="w-3.5 h-3.5 text-gray-300 group-hover:text-primary-400 transition"
                />
            </div>
        </button>
    @endforeach

    {{-- Manage time slots — special services only --}}
    @if ($isSpecial)
        <button
            wire:click="manageSpecialTimes({{ $group->id }})"
            class="w-full flex items-center justify-center gap-1.5 text-[11px] font-medium
                   text-primary-600 hover:text-primary-800 dark:text-primary-400
                   border border-dashed border-primary-300 dark:border-primary-700
                   hover:bg-primary-50 dark:hover:bg-primary-900/20
                   rounded-lg py-1.5 transition"
        >
            <x-heroicon-s-clock class="w-3.5 h-3.5" />
            Manage time slots
        </button>
    @endif
</div>