{{-- livewire/worship-plan-roster-preview.blade.php --}}
{{-- Root div is required by Livewire — must always be present --}}
<div>
    @if ($this->rosterByTime->isNotEmpty())
        <div class="mt-1 space-y-1.5">
            @foreach ($this->rosterByTime as $time => $assignments)
                @if ($assignments->isNotEmpty())
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">

                        {{-- Only show time label if more than one slot --}}
                        @if ($this->rosterByTime->count() > 1)
                            <span class="font-mono font-semibold text-gray-400 dark:text-gray-500 mr-1">
                                {{ $time }}
                            </span>
                        @endif

                        @foreach ($assignments as $assignment)
                            <span class="inline-flex items-center gap-1 mr-2">
                                <x-heroicon-s-user class="w-3 h-3 shrink-0" />
                                <span class="font-medium">{{ $assignment['group_name'] }}:</span>
                                {{ $assignment['individuals'] ?: '—' }}
                            </span>
                        @endforeach

                    </div>
                @endif
            @endforeach

            {{-- Quick-link to roster management --}}
            <a
                href="{{ \Illuminate\Support\Facades\Route::has('filament.admin.people.resources.rosters.index') ? route('filament.admin.people.resources.rosters.index') : '#' }}"
                class="inline-flex items-center gap-1 text-[11px] text-primary-500 hover:text-primary-700 transition mt-0.5"
            >
                <x-heroicon-s-arrow-top-right-on-square class="w-3 h-3" />
                Manage roster
            </a>
        </div>
    @else
        <p class="text-[11px] italic text-gray-400 dark:text-gray-500 mt-1">No roster assigned</p>
    @endif
</div>