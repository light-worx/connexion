<?php

namespace App\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Lightworx\FilamentReports\Reports\BaseReport;
use App\Models\Diaryentry; 
use App\Models\Venue;      

class VenueWeekReport extends BaseReport
{
    protected ?int $venueId = null;
    protected ?string $reportDate = null;
    protected ?string $headerStyle = null; // 'hub' or null (church)

    // Grid geometry (mm, A4 portrait)
    protected const FIRST_HOUR = 7;    // 07h00
    protected const LAST_HOUR  = 22;   // grid ends at 22h00
    protected const ROW_H      = 17;
    protected const GRID_TOP   = 34;
    protected const GRID_LEFT  = 10;
    protected const COLS_LEFT  = 29;   // where the first day column starts
    protected const COL_W      = 25;

    public function __construct()
    {
        parent::__construct();

        $this->AddFont('DejaVuCond', '', 'DejaVuSansCondensed.ttf', true);
        $this->AddFont('DejaVuCond', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
    }

    /**
     * Picked up automatically by FilamentReportsServiceProvider.
     * Optional query string: ?header=hub
     */
    public static function routes(): void
    {
        Route::middleware(['web', 'auth'])
            ->get('/admin/reports/venues/{id}/{reportdate?}/{header?}', [static::class, 'handle'])
            ->name('reports.venue');
    }

    public function setVenue(int $id): static
    {
        $this->venueId = $id;
        return $this;
    }

    public function setDate(?string $date): static
    {
        $this->reportDate = $date;
        return $this;
    }

    public function setHeaderStyle(?string $style): static
    {
        $this->headerStyle = $style;
        return $this;
    }

    protected function resolveVenueId(): int
    {
        return $this->venueId ?? (int) request()->route('id');
    }

    protected function resolveDate(): string
    {
        $date = $this->reportDate
            ?? request()->route('reportdate')
            ?? request('reportdate');

        return ($date && strtotime($date)) ? date('Y-m-d', strtotime($date)) : date('Y-m-d');
    }

    protected function resolveHeaderStyle(): ?string
    {
        return $this->headerStyle ?? request('header');
    }

    protected function getFilename(): string
    {
        return 'venue-' . $this->resolveVenueId() . '-week-' . $this->resolveDate() . '.pdf';
    }

    // This report draws its own heading and uses the full page height.
    public function Header(): void {}
    public function Footer(): void {}

    public function generate(): void
    {
        $venue = Venue::findOrFail($this->resolveVenueId());

        // Monday-to-Sunday week containing the requested date
        $monday = Carbon::parse($this->resolveDate())->startOfWeek(Carbon::MONDAY);
        $days   = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $monday->copy()->addDays($i);
        }

        $title = $venue->venue . ' Bookings: '
            . $days[0]->format('j F') . ' - ' . $days[6]->format('j F Y');

        $this->setReportTitle($title);
        $this->SetAutoPageBreak(false);
        $this->SetFillColor(190, 190, 190);
        $this->AddPage('P');

        $this->drawHeading($title);

        $rows   = self::LAST_HOUR - self::FIRST_HOUR;           // 15
        $top    = self::GRID_TOP;
        $bottom = $top + $rows * self::ROW_H;                    // 289
        $left   = self::GRID_LEFT;
        $right  = self::COLS_LEFT + 7 * self::COL_W;             // 204

        $this->drawGrid($rows, $top, $bottom, $left, $right);

        // One query for the whole week, grouped by date
        $bookings = Diaryentry::with('diarisable')
            ->where('venue_id', $venue->id)
            ->whereDate('diarydatetime', '>=', $days[0]->toDateString())
            ->whereDate('diarydatetime', '<=', $days[6]->toDateString())
            ->get()
            ->groupBy(fn ($b) => substr($b->diarydatetime, 0, 10));

        foreach ($days as $i => $day) {
            $x = self::COLS_LEFT + $i * self::COL_W;

            foreach ($bookings->get($day->toDateString(), []) as $booking) {
                $this->drawBooking($booking, $x, $top);
            }

            // Column divider and day label
            $this->SetDrawColor(0, 0, 0);
            $this->Line($x, $top, $x, $bottom);
            $this->SetFont('DejaVuCond', 'B', 12);
            $this->Text($x + 6, $top - 2, $day->format('D j'));
        }

        $this->Line($right, $top, $right, $bottom);
    }

    protected function drawHeading(string $title): void
    {
        if ($this->resolveHeaderStyle() === 'hub') {
            $heading = 'Westville Community Hub';
            $this->drawImage(public_path('church/images/hublogosmall.png'), 10, 5, 22, 22);
            $this->drawImage(public_path('church/images/hub-qr-code.png'), 185, 10, 18, 18);
            $this->SetFont('DejaVuCond', 'B', 10);
            $this->Text(167, 9, 'www.westville.org.za');
        } else {
            $heading = setting('general.church_name');
            $this->drawImage(public_path('church/images/blacklogo.png'), 10, 0, 25, 25);
        }

        $this->SetFont('DejaVuCond', 'B', 22);
        $this->Text(40, 12, $heading);
        $this->SetFont('DejaVuCond', '', 14);
        $this->Text(40, 20, $title);
    }

    protected function drawImage(string $path, float $x, float $y, float $w, float $h): void
    {
        if (is_file($path)) {
            $this->Image($path, $x, $y, $w, $h);
        }
    }

    protected function drawGrid(int $rows, float $top, float $bottom, float $left, float $right): void
    {
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('DejaVuCond', 'B', 12);

        for ($i = 0; $i <= $rows; $i++) {
            $y = $top + $i * self::ROW_H;
            $this->Line($left, $y, $right, $y);

            if ($i < $rows) {
                $this->Text($left + 3, $y + 6, sprintf('%02dh00', self::FIRST_HOUR + $i));
            }
        }

        $this->Line($left, $top, $left, $bottom);
    }

    protected function drawBooking($booking, float $x, float $gridTop): void
    {
        $startY = $this->timeToY(substr($booking->diarydatetime, 11, 5), $gridTop);
        $endY   = $this->timeToY(substr((string) $booking->endtime, 0, 5), $gridTop);

        if ($endY <= $startY) {
            return;
        }

        $this->SetDrawColor(0, 0, 0);
        $this->Rect($x, $startY, self::COL_W, $endY - $startY, 'DF');

        $label = $this->bookingLabel($booking);
        if ($label !== '') {
            $this->SetFont('DejaVuCond', '', 8);
            $this->SetXY($x, $startY + 1);
            $this->MultiCell(self::COL_W, 4, $label, 0, 'C');
        }
    }

    /** "HH:MM" → Y coordinate, clamped to the visible 07h00–22h00 range. */
    protected function timeToY(string $time, float $gridTop): float
    {
        [$h, $m] = array_pad(explode(':', $time), 2, 0);
        $hours = max(self::FIRST_HOUR, min(self::LAST_HOUR, (int) $h + (int) $m / 60));

        return $gridTop + ($hours - self::FIRST_HOUR) * self::ROW_H;
    }

    protected function bookingLabel($booking): string
    {
        $d = $booking->diarisable;

        if (! $booking->diarisable_id || ! $d) {
            return '';
        }

        $name = $d->tenant ?? $d->groupname ?? $d->event ?? $d->course ?? null;

        if (! $name) {
            return '';
        }

        return $booking->details ? "{$name} ({$booking->details})" : $name;
    }
}