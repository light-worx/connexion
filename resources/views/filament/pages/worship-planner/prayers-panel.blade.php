{{--
    filament/pages/worship-planner/prayers-panel.blade.php
    Variables: $plan (WorshipPlan)
--}}
<div x-data="{ search: '', results: [] }">

    <div class="relative mb-3">
        <x-heroicon-s-magnifying-glass class="absolute left-3 top-2.5 w-4 h-4 text-gray-400 pointer-events-none" />
        <input
            type="text"
            x-model.debounce.300ms="search"
            placeholder="Search prayers by title…"
            class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        />
    </div>

    <div
        x-init="
            $watch('search', async (val) => {
                if (val.length < 2) { results = []; return; }
                const res = await $wire.searchPrayers(val);
                results = res;
            })
        "
        class="mb-4"
    >
        <template x-if="results.length > 0">
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden mb-3">
                <template x-for="prayer in results" :key="prayer.id">
                    <div class="flex items-center justify-between px-3 py-2.5 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="prayer.title"></p>
                            <p class="text-xs text-gray-400" x-text="prayer.type ?? ''"></p>
                        </div>
                        <button
                            x-on:click="$wire.addPrayer(prayer.id); search = ''; results = []"
                            class="text-xs text-primary-600 hover:text-primary-800 font-medium px-2 py-1 rounded hover:bg-primary-50 dark:hover:bg-primary-900/30 transition"
                        >
                            + Add
                        </button>
                    </div>
                </template>
            </div>
        </template>
        <template x-if="search.length >= 2 && results.length === 0">
            <p class="text-sm text-gray-400 italic mb-3">No prayers found.</p>
        </template>
    </div>

    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
        Added to this service
    </h4>

    @php
        $planPrayers = $plan->planItems()
            ->where('itemable_type', \App\Models\Prayer::class)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->with('itemable', 'suggestedBy')
            ->orderBy('position')->orderBy('created_at')
            ->get();
    @endphp

    @forelse ($planPrayers as $item)
        <div wire:key="prayer-item-{{ $item->id }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg border mb-2
            {{ $item->status === 'confirmed'
                ? 'border-success-300 bg-success-50 dark:border-success-700 dark:bg-success-900/20'
                : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800' }}">
            <div class="flex items-center gap-2 min-w-0">
                @if ($item->position)
                    <span class="text-xs font-mono text-gray-400 shrink-0">{{ $item->position }}.</span>
                @endif
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ $item->itemable?->title ?? '—' }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ ucfirst($item->status) }}
                        @if ($item->suggestedBy) · {{ $item->suggestedBy->name }} @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1 shrink-0 ml-2">
                @if ($item->status === 'suggested')
                    <button
                        wire:click="confirmItem({{ $item->id }})"
                        class="text-xs text-success-600 hover:text-success-800 font-bold px-2 py-1 rounded hover:bg-success-50 transition"
                        title="Confirm"
                    >✓</button>
                @endif
                <button
                    wire:click="removeItem({{ $item->id }})"
                    class="text-red-400 hover:text-red-600 px-1.5 py-1 rounded hover:bg-red-50 transition"
                    title="Remove"
                >
                    <x-heroicon-s-trash class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-400 italic">No prayers added yet. Search above to add some.</p>
    @endforelse
</div>