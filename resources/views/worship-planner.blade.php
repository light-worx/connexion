<x-filament-panels::page>
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <x-filament::button wire:click="previousYear" icon="heroicon-o-chevron-left" size="sm" />

            <h1 class="text-2xl font-bold">
                Service plans – {{ $year }}
            </h1>

            <x-filament::button wire:click="nextYear" icon="heroicon-o-chevron-right" size="sm" />
        </div>
    </div>

    @php
        $daysByMonth = collect($this->getPlannerDays())
            ->map(fn ($day, $key) => array_merge($day, ['key' => $key]))
            ->groupBy(fn ($day) => \Carbon\Carbon::parse($day['key'])->month);
    @endphp

    {{-- Grid --}}
    <div class="grid gap-6 md:grid-cols-2">
        @foreach ($daysByMonth as $month => $days)
            <x-filament::section>
                <x-slot name="heading">
                    {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                </x-slot>

                <div class="grid grid-cols-2 gap-3">
                    @foreach ($days as $day)
                        @php
                            $key  = $day['key'];
                            $date = \Carbon\Carbon::parse($key);
                            $plan = $plans[$key] ?? null;
                            $initials = collect($plan['plan_services'] ?? [])
                                ->map(function ($service) {
                                    if (!empty($service['person'])) {
                                        return strtoupper(
                                            substr($service['person']['firstname'], 0, 1) .
                                            substr($service['person']['surname'], 0, 1)
                                        );
                                    }
                                    return null;
                                })
                                ->filter()
                                ->unique()
                                ->values();
                        @endphp

                        <div
                            class="cursor-pointer rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow-md
                                   dark:border-gray-700 dark:bg-gray-800"
                            wire:click="$set('selectedDate', '{{ $key }}'); $wire.mountAction('editPlan')"
                        >
                            {{-- Date + badge --}}
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ $date->format('j M') }}
                                </div>

                                @if ($day['type'] === 'special')
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-800
                                                 dark:bg-amber-900 dark:text-amber-200">
                                        {{ $day['label'] }}
                                    </span>
                                @endif

                                @if ($initials->count())
                                    <div class="text-xs font-semibold text-primary-600 dark:text-primary-400">
                                        {{ $initials->join(', ') }}
                                    </div>
                                @endif
                            </div>

                            @if ($plan)
                                {{-- Series --}}
                                @if (!empty($plan['series']['image']))
                                    <img
                                        src="{{ asset('storage/' . $plan['series']['image']) }}"
                                        class="my-2 h-20 w-full rounded object-cover"
                                    >
                                @endif

                                <div
                                    class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100"
                                    style="border-left: 4px solid {{ $plan['series']['colour'] ?? '#0d9488' }}; padding-left: 0.5rem"
                                >
                                    {{ $plan['series']['series'] ?? 'No series' }}
                                </div>

                                {{-- Shared details --}}
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    @if ($plan['details'])
                                        <i>{{ $plan['details'] }}</i><br>
                                    @endif
                                    @if ($plan['reading'])
                                        <div>
                                            <strong>Reading:</strong> {{ $plan['reading'] }}
                                        </div>
                                    @endif
                                </div>

                                {{-- MULTI-SERVICE DISPLAY --}}
                                @if (!empty($plan['plan_services']))
                                    <div class="mt-3 space-y-2 text-xs text-gray-700 dark:text-gray-300">
                                        @if (count($plan['plan_services'])==1)
                                            1 service
                                        @else
                                            {{count($plan['plan_services'])}} services
                                        @endif
                                    </div>
                                @endif

                                {{-- Service created badge --}}
                                @if (($plan['services_count'] ?? 0) > 0)
                                    <a
                                        href="{{ route(
                                            'filament.admin.worship.resources.services.index',
                                            ['tableFilters[servicedate][value]' => $key]
                                        ) }}"
                                        class="mt-2 inline-flex items-center gap-1 text-xs text-success-600 hover:underline
                                               dark:text-success-400"
                                    >
                                        <x-filament::icon icon="heroicon-m-check-circle" class="h-4 w-4" />
                                        <span>
                                            {{ $plan['services_count'] === 1
                                                ? 'Service created'
                                                : $plan['services_count'] . ' services created'
                                            }}
                                        </span>
                                    </a>
                                @endif
                            @else
                                <div class="mt-2 text-sm italic text-gray-400">
                                    + Assign series
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>