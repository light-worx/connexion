<?php

namespace App\Filament\Pages;

use App\Models\WorshipSundayGroup;
use App\Models\WorshipPlan;
use App\Models\WorshipPlanItem;
use App\Models\Series;
use App\Models\Song;
use App\Models\Prayer;
use App\Services\WorshipPlannerService;
use BackedEnum;
use UnitEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class WorshipPlannerPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string                    $navigationLabel = 'Worship Planner';
    protected static string | UnitEnum | null   $navigationGroup = 'Worship';
    protected static ?int                       $navigationSort  = 1;
    protected string                            $view            = 'filament.pages.worship-planner';

    // ── State ────────────────────────────────────────────────────────────────

    #[Url(as: 'year')]
    public int $year;

    public ?int $editingPlanId = null;

    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(WorshipPlannerService $service): void
    {
        $this->year = (int) request('year', now()->year);
        $this->ensureYearExists($service);
    }

    // ── Data ─────────────────────────────────────────────────────────────────

    public function getMonthsProperty(): Collection
    {
        return WorshipSundayGroup::forYear($this->year)
            ->with(['series', 'plans', 'plans.overrideSeries', 'plans.planItems'])
            ->get()
            ->groupBy(fn ($g) => (int) $g->service_date->format('n'))
            ->sortKeys();
    }

    // ── Year navigation ──────────────────────────────────────────────────────

    public function previousYear(WorshipPlannerService $service): void
    {
        $this->year--;
        $this->ensureYearExists($service);
    }

    public function nextYear(WorshipPlannerService $service): void
    {
        $this->year++;
        $this->ensureYearExists($service);
    }

    private function ensureYearExists(WorshipPlannerService $service): void
    {
        if (WorshipSundayGroup::whereYear('service_date', $this->year)->exists()) {
            return;
        }

        try {
            $result = $service->syncYear($this->year);

            if ($result['groups_created'] > 0) {
                Notification::make()
                    ->title("{$this->year} services generated")
                    ->body("{$result['groups_total']} Sundays · {$result['plans_total']} service slots created.")
                    ->success()
                    ->send();
            }
        } catch (\Throwable $e) {
            \Log::error('WorshipPlannerPage: auto-generation failed', [
                'year' => $this->year, 'message' => $e->getMessage(),
            ]);
            Notification::make()
                ->title("Could not generate {$this->year}")
                ->body($e->getMessage())
                ->danger()->persistent()->send();
        }
    }

    public function generateYear(WorshipPlannerService $service): void
    {
        $this->ensureYearExists($service);
    }

    // ── Plan editor — opened from the blade via mountAction ─────────────────

    /**
     * Called from the card's Edit button: wire:click="editPlan({{ $plan->id }})"
     * Sets the editing ID then mounts the editPlan action so Filament opens the modal.
     */
    public function editPlan(int $planId): void
    {
        $this->editingPlanId = $planId;
        $this->mountAction('editPlan');
    }

    protected function getEditingPlan(): ?WorshipPlan
    {
        if (! $this->editingPlanId) return null;

        return WorshipPlan::with([
            'sundayGroup.series',
            'overrideSeries',
            'planItems.itemable',
        ])->find($this->editingPlanId);
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resync')
                ->label('Re-sync preachers from API')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Re-sync ' . $this->year . ' preachers?')
                ->modalDescription('This will refresh preacher names from the external API for all Sundays in ' . $this->year . '.')
                ->action(function (WorshipPlannerService $service) {
                    $service->resyncPreachers($this->year);
                    Notification::make()->title('Preachers re-synced')->success()->send();
                }),

            $this->getEditPlanAction(),
        ];
    }

    protected function getEditPlanAction(): Action
    {
        return Action::make('editPlan')
            ->label(fn () => $this->getEditingPlan()
                ? $this->getEditingPlan()->service_time . ' — ' . $this->getEditingPlan()->sundayGroup->service_date->format('j M Y')
                : 'Edit Service'
            )
            ->modalWidth('2xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->form(function (): array {
                $plan  = $this->getEditingPlan();
                $group = $plan?->sundayGroup;

                if (! $plan) return [];

                return [
                    // ── Header info (read-only) ──────────────────────────
                    ViewField::make('service_info')
                        ->label('')
                        ->view('filament.pages.worship-planner.plan-modal-header')
                        ->viewData(['plan' => $plan, 'group' => $group]),

                    Tabs::make('Tabs')
                        ->tabs([

                            // ── DETAILS TAB ─────────────────────────────
                            Tab::make('Series & Reading')
                                ->icon('heroicon-s-book-open')
                                ->schema([
                                    Toggle::make('override_defaults')
                                        ->label('Override group defaults')
                                        ->helperText('Enable to give this time-slot its own preacher, series, or reading.')
                                        ->default(! $plan->uses_group_defaults)
                                        ->live()
                                        ->visible($group->plans->count() > 1),

                                    TextInput::make('override_preacher_name')
                                        ->label('Preacher (override)')
                                        ->default($plan->override_preacher_name)
                                        ->visible(fn (Get $get) => $get('override_defaults') && $group->plans->count() > 1)
                                        ->placeholder($group->preacher_name ?? 'Enter preacher name'),

                                    TextInput::make('preacher_display')
                                        ->label('Preacher (from API)')
                                        ->default($group->preacher_name ?? '—')
                                        ->disabled()
                                        ->visible(fn (Get $get) => ! $get('override_defaults') || $group->plans->count() === 1),

                                    Select::make('series_id')
                                        ->label('Sermon Series' . ($group->plans->count() > 1 ? ' (shared across all services this Sunday)' : ''))
                                        ->options(Series::orderBy('series')->pluck('series', 'id'))
                                        ->default($plan->override_series_id ?? $group->series_id)
                                        ->searchable()
                                        ->nullable(),

                                    TextInput::make('bible_reading')
                                        ->label('Bible Reading' . ($group->plans->count() > 1 ? ' (shared across all services this Sunday)' : ''))
                                        ->default($plan->override_bible_reading ?? $group->bible_reading)
                                        ->placeholder('e.g. Romans 8:1-17'),
                                ]),

                            // ── SONGS TAB ────────────────────────────────
                            Tab::make('Songs')
                                ->icon('heroicon-s-musical-note')
                                ->schema([
                                    ViewField::make('songs_panel')
                                        ->view('filament.pages.worship-planner.songs-panel')
                                        ->viewData(['plan' => $plan])
                                        ->label(''),
                                ]),

                            // ── PRAYERS TAB ──────────────────────────────
                            Tab::make('Prayers')
                                ->icon('heroicon-s-hand-raised')
                                ->schema([
                                    ViewField::make('prayers_panel')
                                        ->view('filament.pages.worship-planner.prayers-panel')
                                        ->viewData(['plan' => $plan])
                                        ->label(''),
                                ]),

                            // ── ROSTER TAB ───────────────────────────────
                            Tab::make('Roster')
                                ->icon('heroicon-s-user-group')
                                ->schema([
                                    ViewField::make('roster_panel')
                                        ->view('filament.pages.worship-planner.roster-panel')
                                        ->viewData(['plan' => $plan])
                                        ->label(''),
                                ]),
                        ])
                        ->columnSpanFull(),
                ];
            })
            ->action(function (array $data) {
                $plan  = $this->getEditingPlan();
                $group = $plan?->sundayGroup;
                if ($plan && $group) {
                    $this->saveDetails($plan, $group, $data);
                }
            });
    }

    // ── Save helpers ─────────────────────────────────────────────────────────

    public function saveDetails(WorshipPlan $plan, WorshipSundayGroup $group, array $data): void
    {
        $overriding = ($data['override_defaults'] ?? false) && $group->plans->count() > 1;

        if ($overriding) {
            $plan->update([
                'override_series_id'     => $data['series_id'] ?? null,
                'override_bible_reading' => $data['bible_reading'] ?? null,
                'override_preacher_name' => $data['override_preacher_name'] ?? null,
            ]);
        } else {
            $group->update([
                'series_id'     => $data['series_id'] ?? null,
                'bible_reading' => $data['bible_reading'] ?? null,
            ]);
            $plan->update([
                'override_series_id'     => null,
                'override_bible_reading' => null,
                'override_preacher_name' => null,
            ]);
        }

        Notification::make()->title('Details saved')->success()->send();
    }

    // ── Song / Prayer management (called from view panels via Livewire) ───────

    public function addSong(int $songId): void
    {
        if (! $this->editingPlanId) return;

        $exists = WorshipPlanItem::where('worship_plan_id', $this->editingPlanId)
            ->where('itemable_type', Song::class)
            ->where('itemable_id', $songId)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->exists();

        if ($exists) {
            Notification::make()->title('Song already added')->warning()->send();
            return;
        }

        WorshipPlanItem::create([
            'worship_plan_id'      => $this->editingPlanId,
            'itemable_type'        => Song::class,
            'itemable_id'          => $songId,
            'status'               => 'suggested',
            'suggested_by_user_id' => auth()->id(),
        ]);

        Notification::make()->title('Song added')->success()->send();
    }

    public function addPrayer(int $prayerId): void
    {
        if (! $this->editingPlanId) return;

        $exists = WorshipPlanItem::where('worship_plan_id', $this->editingPlanId)
            ->where('itemable_type', Prayer::class)
            ->where('itemable_id', $prayerId)
            ->whereIn('status', ['suggested', 'confirmed'])
            ->exists();

        if ($exists) {
            Notification::make()->title('Prayer already added')->warning()->send();
            return;
        }

        WorshipPlanItem::create([
            'worship_plan_id'      => $this->editingPlanId,
            'itemable_type'        => Prayer::class,
            'itemable_id'          => $prayerId,
            'status'               => 'suggested',
            'suggested_by_user_id' => auth()->id(),
        ]);

        Notification::make()->title('Prayer added')->success()->send();
    }

    public function removeItem(int $itemId): void
    {
        WorshipPlanItem::findOrFail($itemId)->delete();
    }

    public function confirmItem(int $itemId): void
    {
        $item   = WorshipPlanItem::findOrFail($itemId);
        $maxPos = WorshipPlanItem::where('worship_plan_id', $item->worship_plan_id)
                    ->where('status', 'confirmed')->max('position') ?? 0;
        $item->confirm($maxPos + 1);
    }

    public function rejectItem(int $itemId): void
    {
        WorshipPlanItem::findOrFail($itemId)->reject();
    }

    // ── Search helpers (called from Alpine in songs/prayers panels) ──────────

    public function searchSongs(string $query): array
    {
        if (strlen($query) < 2) return [];

        return Song::where('title', 'like', "%{$query}%")
            ->orWhere('author', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'title', 'author'])
            ->toArray();
    }

    public function searchPrayers(string $query): array
    {
        if (strlen($query) < 2) return [];

        return Prayer::where('title', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'title'])
            ->toArray();
    }

    // ── Page metadata ─────────────────────────────────────────────────────────

    public function getTitle(): string { return 'Worship Planner'; }

    public static function getNavigationBadge(): ?string
    {
        $count = WorshipPlan::where('status', 'draft')
            ->whereHas('sundayGroup', fn ($q) =>
                $q->whereYear('service_date', now()->year)
                  ->where('service_date', '>=', now()->toDateString()))
            ->count();

        return $count > 0 ? (string) $count : null;
    }
}