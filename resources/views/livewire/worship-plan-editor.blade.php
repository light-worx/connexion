<div
    x-data="{ open: true }"
    x-show="open"
    x-on:close-plan-editor.window="open = false; setTimeout(() => $wire.dispatch('plan-editor-closed'), 300)"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex"
>
    {{-- Backdrop --}}
    <div
        class="fixed inset-0 bg-black/40 dark:bg-black/60"
        x-on:click="open = false"
    ></div>

    {{-- Slide-over panel --}}
    <div
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="relative ml-auto w-full max-w-2xl h-full bg-white dark:bg-gray-900 shadow-2xl flex flex-col overflow-hidden"
    >

        {{-- ── Header ──────────────────────────────────────────────── --}}
        <div class="flex items-start justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-0.5">
                    {{ $this->group->service_date->format('l, j F Y') }}
                    @if ($this->group->is_special_service)
                        <span class="ml-1 text-primary-500">★ {{ $this->group->display_name }}</span>
                    @endif
                </p>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $this->plan->service_time }} Service
                </h2>
                @if ($this->plan->effective_preacher)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $this->plan->effective_preacher }}
                    </p>
                @endif
            </div>

            {{-- Status pill + close --}}
            <div class="flex items-center gap-3 mt-1">
                <span @class([
                    'text-xs font-medium px-2.5 py-1 rounded-full',
                    'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' => $this->plan->isDraft(),
                    'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300' => $this->plan->isConfirmed(),
                    'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300' => $this->plan->isPublished(),
                ])>
                    {{ ucfirst($this->plan->status) }}
                </span>

                <button
                    wire:click="close"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                >
                    <x-heroicon-s-x-mark class="w-5 h-5" />
                </button>
            </div>
        </div>

        {{-- ── Tabs ─────────────────────────────────────────────────── --}}
        <div class="flex border-b border-gray-200 dark:border-gray-700 px-6 bg-white dark:bg-gray-900">
            @foreach ([
                'details'  => ['icon' => 'heroicon-s-book-open',     'label' => 'Series & Reading'],
                'songs'    => ['icon' => 'heroicon-s-musical-note',   'label' => 'Songs'],
                'prayers'  => ['icon' => 'heroicon-s-hand-raised',    'label' => 'Prayers'],
                'roster'   => ['icon' => 'heroicon-s-user-group',     'label' => 'Roster'],
            ] as $tab => $meta)
                <button
                    wire:click="$set('activeTab', '{{ $tab }}')"
                    @class([
                        'flex items-center gap-1.5 px-4 py-3 text-sm font-medium border-b-2 -mb-px transition',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === $tab,
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== $tab,
                    ])
                >
                    <x-dynamic-component :component="$meta['icon']" class="w-4 h-4" />
                    {{ $meta['label'] }}
                </button>
            @endforeach
        </div>

        {{-- ── Tab content (scrollable) ─────────────────────────────── --}}
        <div class="flex-1 overflow-y-auto px-6 py-5">

            {{-- ── DETAILS TAB ──────────────────────────────────────── --}}
            @if ($activeTab === 'details')

                {{-- Override toggle (only shown when there are sibling plans on the same Sunday) --}}
                @if ($this->group->plans->count() > 1)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 mb-5">
                        <div>
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Override group defaults</p>
                            <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                                Enable to give this time-slot its own preacher, series, or reading.
                            </p>
                        </div>
                        <button
                            wire:click="$toggle('overrideDefaults')"
                            @class([
                                'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none',
                                'bg-primary-500' => $overrideDefaults,
                                'bg-gray-200 dark:bg-gray-700' => ! $overrideDefaults,
                            ])
                        >
                            <span @class([
                                'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
                                'translate-x-6' => $overrideDefaults,
                                'translate-x-1' => ! $overrideDefaults,
                            ])></span>
                        </button>
                    </div>
                @endif

                {{-- Preacher (override only) --}}
                @if ($overrideDefaults)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Preacher
                        </label>
                        <input
                            type="text"
                            wire:model="overridePreacher"
                            placeholder="Override preacher name…"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        />
                    </div>
                @else
                    <div class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                        <p class="text-xs text-gray-400 mb-0.5">Preacher (from API)</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $this->group->preacher_name ?? '—' }}
                        </p>
                    </div>
                @endif

                {{-- Series --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Sermon Series
                        @if (! $overrideDefaults && $this->group->plans->count() > 1)
                            <span class="text-xs text-gray-400 font-normal ml-1">(shared across all services this Sunday)</span>
                        @endif
                    </label>
                    <select
                        wire:model="seriesId"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                        <option value="">— No series —</option>
                        @foreach (App\Models\Series::orderBy('series')->get() as $s)
                            <option value="{{ $s->id }}">{{ $s->series }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Bible Reading --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Bible Reading
                        @if (! $overrideDefaults && $this->group->plans->count() > 1)
                            <span class="text-xs text-gray-400 font-normal ml-1">(shared across all services this Sunday)</span>
                        @endif
                    </label>
                    <input
                        type="text"
                        wire:model="bibleReading"
                        placeholder="e.g. Romans 8:1-17"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    />
                </div>

                <x-filament::button wire:click="saveDetails" color="primary" icon="heroicon-s-check">
                    Save Details
                </x-filament::button>

            @endif

            {{-- ── SONGS TAB ─────────────────────────────────────────── --}}
            @if ($activeTab === 'songs')

                {{-- Search --}}
                <div class="relative mb-4">
                    <x-heroicon-s-magnifying-glass class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="songSearch"
                        placeholder="Search songs by title or author…"
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    />
                </div>

                {{-- Search results --}}
                @if ($this->songResults->isNotEmpty())
                    <div class="mb-5 rounded-lg border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
                        @foreach ($this->songResults as $song)
                            <div class="flex items-center justify-between px-3 py-2.5 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $song->title }}</p>
                                    @if (isset($song->author) && $song->author)
                                        <p class="text-xs text-gray-400">{{ $song->author }}</p>
                                    @endif
                                </div>
                                <button
                                    wire:click="addSong({{ $song->id }})"
                                    class="text-xs text-primary-600 hover:text-primary-800 font-medium px-2 py-1 rounded hover:bg-primary-50 dark:hover:bg-primary-900/30 transition"
                                >
                                    + Add
                                </button>
                            </div>
                        @endforeach
                    </div>
                @elseif (strlen($songSearch) >= 2)
                    <p class="text-sm text-gray-400 italic mb-4">No songs found for "{{ $songSearch }}"</p>
                @endif

                {{-- Current plan songs --}}
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    Added to this service ({{ $this->planSongs->count() }})
                </h4>

                @forelse ($this->planSongs as $item)
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-lg border mb-2 @if($item->status === 'confirmed') border-success-300 bg-success-50 dark:border-success-700 dark:bg-success-900/20 @else border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 @endif">
                        <div class="flex items-center gap-2">
                            @if ($item->position)
                                <span class="text-xs font-mono text-gray-400 w-5 text-right">{{ $item->position }}.</span>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $item->itemable?->title ?? '—' }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ ucfirst($item->status) }}
                                    @if ($item->suggestedBy) · {{ $item->suggestedBy->name }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            @if ($item->status === 'suggested')
                                <button wire:click="confirmItem({{ $item->id }})" class="text-xs text-success-600 hover:text-success-800 font-medium px-2 py-1 rounded hover:bg-success-50 transition" title="Confirm">
                                    ✓
                                </button>
                            @endif
                            <button wire:click="removeItem({{ $item->id }})" class="text-xs text-red-400 hover:text-red-600 px-2 py-1 rounded hover:bg-red-50 transition" title="Remove">
                                <x-heroicon-s-trash class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">No songs added yet. Search above to add some.</p>
                @endforelse

            @endif

            {{-- ── PRAYERS TAB ───────────────────────────────────────── --}}
            @if ($activeTab === 'prayers')

                <div class="relative mb-4">
                    <x-heroicon-s-magnifying-glass class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="prayerSearch"
                        placeholder="Search prayers by title…"
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    />
                </div>

                @if ($this->prayerResults->isNotEmpty())
                    <div class="mb-5 rounded-lg border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
                        @foreach ($this->prayerResults as $prayer)
                            <div class="flex items-center justify-between px-3 py-2.5 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $prayer->title }}</p>
                                    @if (isset($prayer->type) && $prayer->type)
                                        <p class="text-xs text-gray-400">{{ $prayer->type }}</p>
                                    @endif
                                </div>
                                <button
                                    wire:click="addPrayer({{ $prayer->id }})"
                                    class="text-xs text-primary-600 hover:text-primary-800 font-medium px-2 py-1 rounded hover:bg-primary-50 dark:hover:bg-primary-900/30 transition"
                                >
                                    + Add
                                </button>
                            </div>
                        @endforeach
                    </div>
                @elseif (strlen($prayerSearch) >= 2)
                    <p class="text-sm text-gray-400 italic mb-4">No prayers found for "{{ $prayerSearch }}"</p>
                @endif

                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    Added to this service ({{ $this->planPrayers->count() }})
                </h4>

                @forelse ($this->planPrayers as $item)
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-lg border mb-2 @if($item->status === 'confirmed') border-success-300 bg-success-50 dark:border-success-700 dark:bg-success-900/20 @else border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 @endif">
                        <div class="flex items-center gap-2">
                            @if ($item->position)
                                <span class="text-xs font-mono text-gray-400 w-5 text-right">{{ $item->position }}.</span>
                            @endif
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $item->itemable?->title ?? '—' }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ ucfirst($item->status) }}
                                    @if ($item->suggestedBy) · {{ $item->suggestedBy->name }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1">
                            @if ($item->status === 'suggested')
                                <button wire:click="confirmItem({{ $item->id }})" class="text-xs text-success-600 hover:text-success-800 font-medium px-2 py-1 rounded hover:bg-success-50 transition" title="Confirm">
                                    ✓
                                </button>
                            @endif
                            <button wire:click="removeItem({{ $item->id }})" class="text-xs text-red-400 hover:text-red-600 px-2 py-1 rounded hover:bg-red-50 transition" title="Remove">
                                <x-heroicon-s-trash class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">No prayers added yet. Search above to add some.</p>
                @endforelse

            @endif

            {{-- ── ROSTER TAB ────────────────────────────────────────── --}}
            @if ($activeTab === 'roster')

                @if ($this->rosterItems->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($this->rosterItems as $rosterItem)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                                    {{ $rosterItem->rosterGroup->group->name ?? '—' }}
                                </p>
                                @forelse ($rosterItem->individuals as $individual)
                                    <div class="flex items-center gap-2 py-1">
                                        <x-heroicon-s-user-circle class="w-5 h-5 text-gray-300 dark:text-gray-600 shrink-0" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ $individual->name ?? ($individual->firstname . ' ' . $individual->lastname) }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400 italic">No one assigned yet</p>
                                @endforelse
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <a
                            href="{{ route('filament.admin.resources.rosters.index') }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-800 font-medium"
                        >
                            <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4" />
                            Manage roster
                        </a>
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-user-group class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">No roster assignments found</p>
                        <p class="text-xs text-gray-400">
                            Roster assignments for the {{ $this->plan->service_time }} service on
                            {{ $this->group->service_date->format('j M Y') }} will appear here once set.
                        </p>
                        <a
                            href="{{ route('filament.admin.resources.rosters.index') }}"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 text-sm text-primary-600 hover:text-primary-800 font-medium mt-4"
                        >
                            <x-heroicon-s-arrow-top-right-on-square class="w-4 h-4" />
                            Go to Roster
                        </a>
                    </div>
                @endif

            @endif

        </div>

        {{-- ── Footer ──────────────────────────────────────────────── --}}
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
            <div class="text-xs text-gray-400">
                {{ $this->group->plans->count() }} service(s) this Sunday
                @if (! $this->plan->uses_group_defaults)
                    · <span class="text-amber-500">⚡ Has overrides</span>
                @endif
            </div>
            <x-filament::button wire:click="close" color="gray" size="sm">
                Close
            </x-filament::button>
        </div>

    </div>
</div>