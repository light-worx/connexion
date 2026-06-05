<?php

namespace App\Filament\Pages;

use App\Models\WorshipSundayGroup;
use App\Models\WorshipPlan;
use App\Services\WorshipPlannerService;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class WorshipPlannerPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Worship Planner';
    protected static ?string $navigationGroup = 'Worship';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.worship-planner';

    // ── State ────────────────────────────────────────────────────────────────

    /** Currently displayed year — synced to URL query string */
    #[Url(as: 'year')]
    public int $year;

    /** ID of the plan being edited in the slide-over */
    public ?int $editingPlanId = null;

    /** Controls slide-over visibility */
    public bool $showPlanEditor = false;

    // ── Lifecycle ────────────────────────────────────────────────────────────

    public function mount(WorshipPlannerService $service): void
    {
        $this->year = (int) request('year', now()->year);
        $this->syncYear($service);
    }

    // ── Data ─────────────────────────────────────────────────────────────────

    /**
     * Groups for the current year, grouped by month, for the view.
     * Returns Collection<int, Collection<WorshipSundayGroup>>
     */
    public function getMonthsProperty(): Collection
    {
        return WorshipSundayGroup::forYear($this->year)
            ->with([
                'series',
                'plans',
                'plans.overrideSeries',
                'plans.planItems',
            ])
            ->get()
            ->groupBy(fn ($g) => $g->service_date->format('n'))
            ->sortKeys();
    }

    // ── Year navigation ──────────────────────────────────────────────────────

    public function previousYear(WorshipPlannerService $service): void
    {
        $this->year--;
        $this->syncYear($service);
    }

    public function nextYear(WorshipPlannerService $service): void
    {
        $this->year++;
        $this->syncYear($service);
    }

    private function syncYear(WorshipPlannerService $service): void
    {
        // Only sync if we have no groups yet for this year (avoids repeat API calls)
        $exists = WorshipSundayGroup::whereYear('service_date', $this->year)->exists();

        if (! $exists) {
            try {
                $service->syncYear($this->year);
            } catch (\Throwable $e) {
                Notification::make()
                    ->title('Could not sync service dates')
                    ->body('Some dates may be missing. ' . $e->getMessage())
                    ->warning()
                    ->send();
            }
        }
    }

    // ── Plan editor slide-over ───────────────────────────────────────────────

    public function openPlanEditor(int $planId): void
    {
        $this->editingPlanId = $planId;
        $this->showPlanEditor = true;
        $this->dispatch('open-plan-editor', planId: $planId);
    }

    public function closePlanEditor(): void
    {
        $this->showPlanEditor = false;
        $this->editingPlanId = null;
    }

    // ── Page-level actions (header) ──────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resync')
                ->label('Re-sync from API')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Re-sync ' . $this->year . ' from API?')
                ->modalDescription('This will refresh preacher names and special service dates from the external API.')
                ->action(function (WorshipPlannerService $service) {
                    $service->syncYear($this->year);
                    Notification::make()
                        ->title('Sync complete')
                        ->success()
                        ->send();
                }),
        ];
    }

    // ── Page metadata ────────────────────────────────────────────────────────

    public function getTitle(): string
    {
        return 'Worship Planner';
    }

    public static function getNavigationBadge(): ?string
    {
        // Show count of unplanned upcoming Sundays as a badge
        $unpublished = WorshipPlan::where('status', 'draft')
            ->whereHas('sundayGroup', fn ($q) => $q->whereYear('service_date', now()->year)
                ->where('service_date', '>=', now()->toDateString()))
            ->count();

        return $unpublished > 0 ? (string) $unpublished : null;
    }
}