{{-- livewire/plan-songs-panel.blade.php --}}
<div>
    {{-- Search --}}
    <div class="relative mb-3">
        <x-heroicon-s-magnifying-glass class="absolute left-3 top-2.5 w-4 h-4 text-gray-400 pointer-events-none" />
        <input
            type="text"
            wire:model.live.debounce.300ms="songSearch"
            placeholder="Search songs by title or author…"
            class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600
                   bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm
                   focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        />
    </div>

    {{-- Search results --}}
    @if ($this->songResults->isNotEmpty())
        <div class="mb-4 rounded-lg border border-gray-200 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden">
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
                        class="text-xs text-primary-600 hover:text-primary-800 font-medium px-2 py-1 rounded
                               hover:bg-primary-50 dark:hover:bg-primary-900/30 transition"
                    >+ Add</button>
                </div>
            @endforeach
        </div>
    @elseif (strlen($songSearch) >= 2)
        <p class="text-sm text-gray-400 italic mb-3">No songs found for "{{ $songSearch }}"</p>
    @endif

    {{-- Added songs --}}
    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
        Added to this service ({{ $this->planSongs->count() }})
    </h4>

    @forelse ($this->planSongs as $item)
        <div wire:key="song-{{ $item->id }}"
             class="flex items-center justify-between px-3 py-2.5 rounded-lg border mb-2
                    {{ $item->status === 'confirmed'
                        ? 'border-success-300 bg-success-50 dark:border-success-700 dark:bg-success-900/20'
                        : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800' }}"
        >
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
                        class="text-xs text-success-600 hover:text-success-800 font-bold px-2 py-1
                               rounded hover:bg-success-50 transition"
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
        <p class="text-sm text-gray-400 italic">No songs added yet. Search above to add some.</p>
    @endforelse
</div>