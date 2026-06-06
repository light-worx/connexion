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
     * Ensure all Sundays for a given year exist in worship_sunday_groups,
     * each with one worship_plan per configured service time.
     *
     * Preacher names are fetched individually per time+date from the API.
     * Special/midweek services are not yet supported by the API — they will
     * be added in a future iteration.
     *
     * Safe to call multiple times — uses firstOrCreate/updateOrCreate throughout.
     */
    /**
     * Generate all Sunday groups and plans for a year.
     * Returns the number of new WorshipSundayGroup records created.
     * Throws on hard failures (e.g. DB errors); API failures are logged but do not halt generation.
     */
    public function syncYear(int $year): array
    {
        $serviceTimes = $this->getServiceTimes();

        if ($serviceTimes->isEmpty()) {
            throw new \RuntimeException(
                "No service times found. Please check that setting('services') returns a non-empty array."
            );
        }

        $groupsCreated = 0;
        $plansCreated  = 0;
        $groupsTotal   = 0;
        $plansTotal    = 0;

        foreach ($this->generateSundays($year) as $date) {

            $group = WorshipSundayGroup::firstOrCreate(
                ['service_date' => $date],
                [
                    'is_special_service' => false,
                    'display_name'       => null,
                ]
            );

            if ($group->wasRecentlyCreated) {
                $groupsCreated++;
            }
            $groupsTotal++;

            foreach ($serviceTimes as $time) {
                $plan = WorshipPlan::firstOrCreate(
                    [
                        'worship_sunday_group_id' => $group->id,
                        'service_time'            => $time,
                    ],
                    ['status' => 'draft']
                );

                if ($plan->wasRecentlyCreated) {
                    $plansCreated++;

                    // Fetch preacher for newly created plans only — non-fatal
                    $preacher = $this->fetchPreacher($time, $date);
                    if ($preacher && ! $group->preacher_name) {
                        $group->update(['preacher_name' => $preacher]);
                    }
                }
                $plansTotal++;
            }
        }

        \Log::info("WorshipPlannerService::syncYear completed", [
            'year'          => $year,
            'groups_created'=> $groupsCreated,
            'groups_total'  => $groupsTotal,
            'plans_created' => $plansCreated,
            'plans_total'   => $plansTotal,
            'times'         => $serviceTimes->all(),
        ]);

        return [
            'groups_created' => $groupsCreated,
            'groups_total'   => $groupsTotal,
            'plans_created'  => $plansCreated,
            'plans_total'    => $plansTotal,
        ];
    }

    /**
     * Re-sync preacher names for all groups in a given year from the API.
     * Fetches the external_id from the API, resolves the name from persons table,
     * and updates both preacher_name and preacher_api_id on the group.
     */
    public function resyncPreachers(int $year): void
    {
        $firstTime = $this->getServiceTimes()->first();

        if (! $firstTime) return;

        WorshipSundayGroup::forYear($year)->each(function (WorshipSundayGroup $group) use ($firstTime) {
            $date = $group->service_date->format('Y-m-d');
            $name = $this->fetchPreacher($firstTime, $date);

            if ($name) {
                $group->update([
                    'preacher_name' => $name,
                    'api_synced_at' => now(),
                ]);
            }
        });
    }

    /**
     * Load all groups for a year, grouped by month number (1–12).
     * Returns Collection<int, Collection<WorshipSundayGroup>>
     */
    public function getGroupedByMonth(int $year): Collection
    {
        return WorshipSundayGroup::forYear($year)
            ->with(['plans.planItems', 'plans.overrideSeries', 'series'])
            ->get()
            ->groupBy(fn ($g) => (int) $g->service_date->format('n'))
            ->sortKeys();
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Generate all Sunday dates for a given year as 'Y-m-d' strings.
     */
    private function generateSundays(int $year): Collection
    {
        $jan1 = Carbon::create($year, 1, 1);

        // Start from the first Sunday of the year
        // Carbon::next(SUNDAY) always moves forward, so handle Jan 1 = Sunday explicitly
        $firstSunday = $jan1->isSunday() ? $jan1->copy() : $jan1->next(Carbon::SUNDAY);

        return collect(
            CarbonPeriod::create($firstSunday, '1 week', "{$year}-12-31")
        )->map(fn (Carbon $d) => $d->format('Y-m-d'));
    }

    /**
     * Fetch the preacher name for a specific time + date from the API.
     *
     * URL format: https://methodist.church.net.za/preacherid/{society_id}/{time}/{date}
     * The API returns a plain numeric ID (the person's external_id).
     * We resolve that to a full name via the persons table.
     *
     * Returns the preacher's full name string, or null on failure.
     */
    private function fetchPreacher(string $time, string $date): ?string
    {
        $societyId = setting('society_id');

        if (! $societyId) {
            \Log::warning('WorshipPlannerService: society_id not set in settings');
            return null;
        }

        $url = "https://methodist.church.net.za/preacherid/{$societyId}/{$time}/{$date}";

        try {
            $response = Http::timeout(8)->get($url);

            if ($response->failed()) {
                \Log::warning("WorshipPlannerService: preacher API returned {$response->status()} for {$time} {$date}");
                return null;
            }

            $externalId = (int) trim($response->body());

            if (! $externalId) {
                return null;
            }

            // Look up the person by their external_id
            $person = \App\Models\Person::where('external_id', $externalId)->first();

            if (! $person) {
                \Log::warning("WorshipPlannerService: no person found with external_id {$externalId} for {$time} {$date}");
                return null;
            }

            return trim("{$person->firstname} {$person->surname}");

        } catch (\Throwable $e) {
            \Log::error("WorshipPlannerService: preacher fetch failed for {$time} {$date} — {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Read configured service times from the setting() helper.
     * setting('services') returns e.g. ['07h30', '09h00', '18h30']
     */
    private function getServiceTimes(): Collection
    {
        $times = setting('services');

        return collect(is_array($times) ? $times : [$times])
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values();
    }
}