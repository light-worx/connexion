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
     * Sync all Sundays and midweek services for a year.
     *
     * Uses two bulk API calls:
     *   1. /api/public/societies/{id}/preachers/{year}  — all Sunday appointments
     *   2. /api/public/societies/{id}/midweeks/{year}   — special/midweek dates
     *
     * Returns counts for notification display.
     */
    public function syncYear(int $year): array
    {
        $configuredTimes = $this->getServiceTimes();

        if ($configuredTimes->isEmpty()) {
            throw new \RuntimeException(
                "No service times configured. Check that setting('services') returns a non-empty array."
            );
        }

        $groupsCreated = 0;
        $plansCreated  = 0;

        // ── 1. Fetch all Sunday preacher appointments from API ────────────────
        $appointments = $this->fetchYearAppointments($year);

        // Group by date so we can process each Sunday together
        $byDate = $appointments->groupBy('date');

        // ── 2. Generate all Sundays for the year ─────────────────────────────
        foreach ($this->generateSundays($year) as $date) {
            $group = WorshipSundayGroup::firstOrCreate(
                ['service_date' => $date],
                ['is_special_service' => false, 'display_name' => null]
            );

            if ($group->wasRecentlyCreated) {
                $groupsCreated++;
            }

            // Appointments for this date, keyed by normalised time (07h30 format)
            $dayAppointments = ($byDate[$date] ?? collect())
                ->keyBy(fn ($a) => $this->normaliseTime($a['time']));

            // Only process times that are configured in system settings
            foreach ($configuredTimes as $time) {
                $appointment = $dayAppointments[$time] ?? null;

                // Update preacher on the group from the first configured time slot
                if ($appointment && $time === $configuredTimes->first() && ! $group->preacher_name) {
                    $person = $this->resolvePersonFromName($appointment['preacher'] ?? '');
                    $group->update([
                        'preacher_name'      => $appointment['preacher'] ?? null,
                        'preacher_person_id' => $person?->id,
                    ]);
                }

                $plan = WorshipPlan::firstOrCreate(
                    ['worship_sunday_group_id' => $group->id, 'service_time' => $time],
                    [
                        'status'       => 'draft',
                        'service_type' => $appointment['service_type'] ?? null,
                    ]
                );

                if ($plan->wasRecentlyCreated) {
                    $plansCreated++;
                } elseif ($appointment && $plan->service_type !== $appointment['service_type']) {
                    // Update service_type if API has changed it
                    $plan->update(['service_type' => $appointment['service_type'] ?? null]);
                }
            }
        }

        // ── 3. Fetch and create midweek / special services ────────────────────
        $midweeks = $this->fetchMidweeks($year);

        foreach ($midweeks as $name => $date) {
            $group = WorshipSundayGroup::firstOrCreate(
                ['service_date' => $date],
                [
                    'is_special_service' => true,
                    'display_name'       => $name,
                    // Midweeks start with no custom times — staff configure them
                    'custom_times'       => null,
                ]
            );

            if ($group->wasRecentlyCreated) {
                $groupsCreated++;

                // Create one default plan at the first configured time
                // Staff can add more time slots via the planner UI
                WorshipPlan::firstOrCreate(
                    ['worship_sunday_group_id' => $group->id, 'service_time' => $configuredTimes->first()],
                    ['status' => 'draft']
                );
                $plansCreated++;
            }
        }

        \Log::info('WorshipPlannerService::syncYear completed', [
            'year'          => $year,
            'groups_created'=> $groupsCreated,
            'plans_created' => $plansCreated,
        ]);

        return [
            'groups_created' => $groupsCreated,
            'groups_total'   => WorshipSundayGroup::whereYear('service_date', $year)->count(),
            'plans_created'  => $plansCreated,
            'plans_total'    => WorshipPlan::whereHas('sundayGroup', fn ($q) => $q->whereYear('service_date', $year))->count(),
        ];
    }

    /**
     * Re-sync preacher names and service types for all plans in a year.
     * Uses the bulk API — one HTTP call for the whole year.
     */
    public function resyncPreachers(int $year): int
    {
        $configuredTimes = $this->getServiceTimes();
        $appointments    = $this->fetchYearAppointments($year);
        $byDate          = $appointments->groupBy('date');
        $updated         = 0;

        WorshipSundayGroup::forYear($year)
            ->where('is_special_service', false)
            ->with('plans')
            ->each(function (WorshipSundayGroup $group) use ($byDate, $configuredTimes, &$updated) {
                $date            = $group->service_date->format('Y-m-d');
                $dayAppointments = ($byDate[$date] ?? collect())
                    ->keyBy(fn ($a) => $this->normaliseTime($a['time']));

                // Update preacher on group from first configured time
                $firstAppt = $dayAppointments[$configuredTimes->first()] ?? null;
                if ($firstAppt && isset($firstAppt['preacher'])) {
                    $person = $this->resolvePersonFromName($firstAppt['preacher']);
                    $group->update([
                        'preacher_name'      => $firstAppt['preacher'],
                        'preacher_person_id' => $person?->id,
                        'api_synced_at'      => now(),
                    ]);
                }

                // Update service_type on each plan
                foreach ($group->plans as $plan) {
                    $appt = $dayAppointments[$plan->service_time] ?? null;
                    if ($appt) {
                        $plan->update(['service_type' => $appt['service_type'] ?? null]);
                        $updated++;
                    }
                }
            });

        return $updated;
    }

    /**
     * Attempt to resolve a Person model from a full name string.
     * Tries firstname + surname match. Returns null if not found.
     */
    private function resolvePersonFromName(string $fullName): ?\App\Models\Person
    {
        $parts = explode(' ', trim($fullName));

        // Strip titles (Rev, Mr, Mrs, Ms, Dr, etc.)
        $titles = ['rev', 'mr', 'mrs', 'ms', 'dr', 'prof', 'pastor', 'elder'];
        while (! empty($parts) && in_array(strtolower(rtrim($parts[0], '.')), $titles)) {
            array_shift($parts);
        }

        if (count($parts) < 2) return null;

        $firstname = array_shift($parts);
        $surname   = implode(' ', $parts); // handles double-barrel surnames

        return \App\Models\Person::where('firstname', 'like', $firstname)
            ->where('surname', 'like', $surname)
            ->first();
    }

    /** @deprecated — kept for backwards compat, not used by new API */
    private function resolveExternalId(string $name): int { return 0; }

    /**
     * Load all groups for a year grouped by month number (1–12).
     */
    public function getGroupedByMonth(int $year): Collection
    {
        return WorshipSundayGroup::forYear($year)
            ->with(['plans.planItems', 'plans.overrideSeries', 'series'])
            ->get()
            ->groupBy(fn ($g) => (int) $g->service_date->format('n'))
            ->sortKeys();
    }

    // ── API calls ────────────────────────────────────────────────────────────

    /**
     * Fetch all Sunday appointments for a year.
     * Returns a flat Collection of appointment arrays:
     *   ['date' => '2026-01-04', 'time' => '07h30', 'service_type' => 'COM', 'preacher' => 'Rev Smith']
     */
    private function fetchYearAppointments(int $year): Collection
    {
        $societyId = setting('society_id');

        if (! $societyId) {
            \Log::warning('WorshipPlannerService: society_id not set');
            return collect();
        }

        $url = "https://methodist.church.net.za/api/public/societies/{$societyId}/preachers/{$year}";

        try {
            $response = Http::timeout(15)->get($url);

            if ($response->failed()) {
                \Log::warning("WorshipPlannerService: appointments API returned {$response->status()}");
                return collect();
            }

            return collect($response->json('appointments', []))
                ->map(fn (array $a) => [
                    'date'         => $a['date'],
                    'time'         => $this->normaliseTime($a['time']),
                    'service_type' => $a['service_type'] ?? null,
                    'preacher'     => $a['preacher'] ?? null,
                ]);

        } catch (\Throwable $e) {
            \Log::error("WorshipPlannerService: appointments API failed — {$e->getMessage()}");
            return collect();
        }
    }

    /**
     * Fetch midweek / special service dates for a year.
     * Returns ['Good Friday' => '2026-04-03', 'Christmas Day' => '2026-12-25', ...]
     */
    private function fetchMidweeks(int $year): array
    {
        $societyId = setting('society_id');

        if (! $societyId) return [];

        $url = "https://methodist.church.net.za/api/public/societies/{$societyId}/midweeks/{$year}";

        try {
            $response = Http::timeout(10)->get($url);

            if ($response->failed()) {
                \Log::warning("WorshipPlannerService: midweeks API returned {$response->status()}");
                return [];
            }

            return $response->json('midweeks', []);

        } catch (\Throwable $e) {
            \Log::error("WorshipPlannerService: midweeks API failed — {$e->getMessage()}");
            return [];
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Convert various time formats to the app's canonical format (07h30).
     * Handles: "07:30", "07:30:00", "7:30", "07h30"
     */
    private function normaliseTime(string $time): string
    {
        // Already in 07h30 format
        if (preg_match('/^\d{2}h\d{2}$/', $time)) {
            return $time;
        }

        // Parse HH:MM or HH:MM:SS
        $parts = explode(':', $time);
        if (count($parts) >= 2) {
            return str_pad($parts[0], 2, '0', STR_PAD_LEFT) . 'h' . $parts[1];
        }

        return $time;
    }

    private function generateSundays(int $year): Collection
    {
        $jan1        = Carbon::create($year, 1, 1);
        $firstSunday = $jan1->isSunday() ? $jan1->copy() : $jan1->next(Carbon::SUNDAY);

        return collect(
            CarbonPeriod::create($firstSunday, '1 week', "{$year}-12-31")
        )->map(fn (Carbon $d) => $d->format('Y-m-d'));
    }

    private function getServiceTimes(): Collection
    {
        $times = setting('services');

        return collect(is_array($times) ? $times : [$times])
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values();
    }
}