<?php

namespace App\Reports;

use Illuminate\Support\Facades\Route;
use Lightworx\FilamentReports\Reports\BaseReport;
use Lightworx\FilamentReports\Reports\Concerns\HasSections;
use Lightworx\FilamentReports\Reports\Concerns\HasTables;
use App\Models\WorshipSundayGroup;

class WorshipPlanReport extends BaseReport
{
    use HasSections, HasTables;

    protected int $year;

    public function __construct()
    {
        parent::__construct();
        $this->config['footer']['enabled']     = false;
        $this->config['page']['orientation']   = 'P';  // L = Landscape, P = Portrait
        $this->config['page']['format']        = 'A4';
    }

    public static function routes(): void
    {
        Route::get('/admin/worship/reports/plan/{year}', function (int $year) {
            return (new static())->setYear($year)->handle();
        })->name('reports.worship-plan')
          ->middleware(['web', 'auth']);
    }

    public function setYear(int $year): static
    {
        $this->year = $year;
        return $this;
    }

    public function generate(): void
    {
        $this->setReportTitle(setting('church_name') . ' - Worship Plan ' . $this->year);
        $this->AddPage('P');

        $groups = WorshipSundayGroup::forYear($this->year)
            ->with(['series', 'plans.overrideSeries'])
            ->orderBy('service_date')
            ->get();

        if ($groups->isEmpty()) {
            $this->renderText('No services found for ' . $this->year . '.');
            return;
        }

        $rows = [];

        foreach ($groups as $group) {
            $plans = $group->plans->sortBy('service_time');

            if ($plans->isEmpty()) {
                continue;
            }

            // Resolve effective values per plan
            $resolved = $plans->map(fn ($plan) => [
                'time'     => $plan->service_time,
                'series'   => $plan->overrideSeries?->series ?? $group->series?->series ?? '',
                'preacher' => $plan->override_preacher_name ?? $group->preacher_name ?? '',
                'reading'  => $plan->override_bible_reading ?? $group->bible_reading ?? '',
            ]);

            // Date label
            $date = $group->service_date->format('j M');

            // Check if all slots share the same preacher, reading and series
            $allSame = $resolved->unique(fn ($r) => $r['preacher'] . '|' . $r['reading'] . '|' . $r['series'])->count() === 1;

            if ($allSame) {
                // Collapse into one row — no time needed
                $first = $resolved->first();
                $series = $first['series'] ?: '';
                if ($group->is_special_service && $group->display_name) {
                    $series .= " " . $group->display_name;
                }
                $rows[] = [
                    $date,
                    $series,
                    $first['preacher'] ?: '',
                    $first['reading']  ?: '',
                ];
            } else {
                // Different details per slot — show time in the date cell
                foreach ($resolved as $slot) {
                    $rows[] = [
                        $date ? "{$date}\n{$slot['time']}" : $slot['time'],
                        $slot['series']   ?: '',
                        $slot['preacher'] ?: '',
                        $slot['reading']  ?: '',
                    ];
                    $date = '';
                }
            }
        }

        $this->renderTable(
            headers: ['Date', 'Series', 'Preacher', 'Reading'],
            rows: $rows,
            columnWidths: [15, 75, 35, 55]
        );
    }

    protected function getFilename(): string
    {
        return 'worship-plan-' . $this->year . '.pdf';
    }
}