<?php

namespace App\Services;

use App\Models\WorshipSundayGroup;
use App\Models\WorshipPlan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class WorshipPlannerService
{
    /**
     * Ensure all Sundays and API special dates for a given year exist in
     * worship_sunday_groups, and that each group has one worship_plan per
     * configured service time.
     *
     * Safe to call multiple times — uses upsert / firstOrCreate throughout.
     */
    public function syncYear(int $year): void
    {
        $serviceTimes = $this->getServiceTimes();

        // 1. Generate all Sundays for the year
        $sundays = $this->generateSundays($year);

        // 2. Fetch special dates from external API
        $specialDates = $this->fetchSpecialDates($year);

        // 3. Merge (special dates override/supplement Sundays if same date)
        $allDates = $sundays->keyBy('date')
                            ->merge($specialDates->keyBy('date'))
                            ->values();

        // 4. Upsert worship_sunday_groups
        foreach ($allDates as $entry) {
            $group = WorshipSundayGroup::updateOrCreate(
                ['service_date' => $entry['date']],
                [
                    'display_name'       => $entry['display_name'] ?? null,
                    'is_special_service' => $entry['is_special'] ?? false,
                    'preacher_name'      => $entry['preacher'] ?? null,
                    'preacher_api_id'    => $entry['preacher_id'] ?? null,
                    'api_synced_at'      => $entry['is_special'] ? now() : null,
                    'api_raw'            => $entry['raw'] ?? null,
                ]
            );

            // 5. Ensure one worship_plan per service time per group
            //    Special services get one plan at a nominal time (or the first configured time)
            $times = $entry['is_special'] ?? false
                ? [$serviceTimes->first() ?? '09:00']
                : $serviceTimes->all();

            foreach ($times as $time) {
                WorshipPlan::firstOrCreate(
                    [
                        'worship_sunday_group_id' => $group->id,
                        'service_time'            => $time,
                    ],
                    ['status' => 'draft']
                );
            }
        }
    }

    /**
     * Load all groups for a year, grouped by month, with plans eager-loaded.
     * Returns a Collection keyed by month number (1–12), each value being
     * a Collection of WorshipSundayGroup models.
     */
    public function getGroupedByMonth(int $year): Collection
    {
        return WorshipSundayGroup::forYear($year)
            ->with(['plans.planItems', 'plans.overrideSeries', 'series'])
            ->get()
            ->groupBy(fn ($g) => $g->service_date->format('n')) // key by month number
            ->sortKeys();
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function generateSundays(int $year): Collection
    {
        return collect(
            CarbonPeriod::create("$year-01-01", '1 week', "$year-12-31")
                        ->filter(fn ($d) => $d->isSunday())
        )->map(fn (Carbon $date) => [
            'date'       => $date->toDateString(),
            'is_special' => false,
            'display_name' => null,
        ]);
    }

    /**
     * Fetch special service dates from your external API.
     * Adapt the URL, auth headers, and response mapping to your actual API.
     */
    private function fetchSpecialDates(int $year): Collection
    {
        try {
            $response = Http::timeout(10)
                ->get(config('worship.api_url') . '/special-dates', [
                    'year' => $year,
                ]);

            if ($response->failed()) {
                \Log::warning("WorshipPlannerService: API returned {$response->status()} for year $year");
                return collect();
            }

            // Expected API shape:
            // [{ "date": "2026-12-25", "name": "Christmas Day", "preacher": "Rev Smith", "preacher_id": "abc123" }]
            return collect($response->json())->map(fn (array $item) => [
                'date'         => $item['date'],
                'display_name' => $item['name']      ?? null,
                'preacher'     => $item['preacher']   ?? null,
                'preacher_id'  => $item['preacher_id'] ?? null,
                'is_special'   => true,
                'raw'          => $item,
            ]);

        } catch (\Throwable $e) {
            \Log::error("WorshipPlannerService: API fetch failed — {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Read configured service times from system settings.
     * Expects a comma-separated string or array, e.g. "07h30,09h00,18h30".
     * Adjust the SystemSetting call to match your settings implementation.
     */
    private function getServiceTimes(): Collection
    {
        $raw = \App\Models\SystemSetting::get('sunday_service_times', '09h00');

        return collect(is_array($raw) ? $raw : explode(',', $raw))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values();
    }
}