<x-filament-panels::page>

    {{-- ── Year Navigation + Full Year Toggle ─────────────────────── --}}
    <div class="flex items-center gap-3 mb-6">
        <x-filament::button
            wire:click="previousYear"
            icon="heroicon-s-chevron-left"
            icon-position="before"
            size="sm"
            color="primary"
            label=""
        />

        <h2 class="text-xl font-bold text-gray-900 dark:text-white min-w-[160px] text-center">
            Service plans – {{ $this->year }}
        </h2>

        <x-filament::button
            wire:click="nextYear"
            icon="heroicon-s-chevron-right"
            icon-position="before"
            size="sm"
            color="primary"
            label=""
        />

        {{-- Full year toggle — current year only --}}
        @if ($this->year === now()->year)
            <div class="ml-auto flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                    {{ $showFullYear ? 'Full year' : 'From ' . now()->format('M') }}
                </span>
                <button
                    wire:click="toggleFullYear"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none
                           {{ $showFullYear ? 'bg-primary-500' : 'bg-gray-300 dark:bg-gray-600' }}"
                    title="{{ $showFullYear ? 'Showing full year' : 'Showing from ' . now()->format('F') . ' onwards' }}"
                >
                    <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform
                                 {{ $showFullYear ? 'translate-x-4' : 'translate-x-1' }}">
                    </span>
                </button>
            </div>
        @endif
    </div>

    {{-- ── Empty state ──────────────────────────────────────────────── --}}
    @if ($this->months->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                <x-heroicon-o-calendar-days class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                No services for {{ $this->year }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-sm">
                If this is unexpected, check that <code class="text-xs bg-gray-100 dark:bg-gray-700 px-1 rounded">setting('services')</code> returns your service times.
            </p>
            <x-filament::button
                wire:click="generateYear"
                icon="heroicon-s-arrow-path"
                color="gray"
                size="sm"
            >
                Retry generation
            </x-filament::button>
        </div>

    {{-- ── Month Grid ───────────────────────────────────────────────── --}}
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($this->months as $monthNumber => $groups)
                @php
                    $monthName = \Carbon\Carbon::create($this->year, $monthNumber)->format('F');
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4">
                        {{ $monthName }}
                    </h3>

                    <div class="grid grid-cols-2 gap-3">
                        @foreach ($groups as $group)
                            @include('filament.pages.worship-planner.partials.service-card', ['group' => $group])
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-filament-panels::page>