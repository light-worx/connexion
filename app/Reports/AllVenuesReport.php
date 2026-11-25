<?php

namespace App\Reports;

use Illuminate\Support\Facades\Route;
use Lightworx\FilamentReports\Reports\BaseReport;
use App\Models\Diaryentry; // adjust to wherever your models live
use App\Models\Venue;      // adjust

class AllVenuesReport extends BaseReport
{
    protected ?string $reportDate = null;

    // Grid geometry (mm, A4 landscape)
    protected const FIRST_HOUR = 7;    // 07h00
    protected const LAST_HOUR  = 22;   // grid ends at 22h00
    protected const ROW_H      = 11;
    protected const GRID_TOP   = 34;
    protected const GRID_LEFT  = 10;
    protected const GRID_RIGHT = 287;  // 297 - 10
    protected const TIME_COL_W = 19;

    public function __construct()
    {
        parent::__construct();

        // Same fonts as the old controller (tFPDF ships these)
        $this->AddFont('DejaVuCond', '', 'DejaVuSansCondensed.ttf', true);
        $this->AddFont('DejaVuCond', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
    }

    public static function routes(): void
    {
        Route::middleware(['web', 'auth'])
            ->get('/admin/reports/venues/{reportdate?}', [static::class, 'handle'])
            ->name('reports.allvenues');
    }

    public function setDate(?string $date): static
    {
        $this->reportDate = $date;
        return $this;
    }

    protected function resolveDate(): string
    {
        $date = $this->reportDate
            ?? request()->route('reportdate')
            ?? request('reportdate');

        return ($date && strtotime($date)) ? date('Y-m-d', strtotime($date)) : date('Y-m-d');
    }

    protected function getFilename(): string
    {
        return 'venue-bookings-' . $this->resolveDate() . '.pdf';
    }

    // This report draws its own heading and uses the full page height.
    public function Header(): void {}
    public function Footer(): void {}

    public function generate(): void
    {
        $date  = $this->reportDate = $this->resolveDate();
        $title = 'Venue Bookings: ' . date('j F Y', strtotime($date));

        $venues = Venue::where('resource', 1)->orderBy('venue')->get();

        $this->setReportTitle($title);
        $this->SetAutoPageBreak(false);
        $this->SetFillColor(190, 190, 190);
        $this->AddPage('L');

        $this->drawHeading($title);

        if ($venues->isEmpty()) {
            return;
        }

        $rows    = self::LAST_HOUR - self::FIRST_HOUR;                 // 15
        $top     = self::GRID_TOP;
        $bottom  = $top + $rows * self::ROW_H;                         // 199
        $left    = self::GRID_LEFT;
        $colsX   = $left + self::TIME_COL_W;                           // 29
        $colW    = floor((self::GRID_RIGHT - $colsX) / $venues->count());
        $right   = $colsX + $colW * $venues->count();

        $this->drawGrid($rows, $top, $bottom, $left, $right);

        foreach ($venues as $i => $venue) {
            $x = $colsX + $i * $colW;

            $this->SetFont('DejaVuCond', 'B', 10);
            $this->SetXY($x, $top - 7);
            $this->Cell($colW, 5, $venue->venue, 0, 0, 'C');

            $this->Line($x, $top - 7, $x, $bottom);

            $bookings = Diaryentry::with('diarisable')
                ->whereDate('diarydatetime', $date)
                ->where('venue_id', $venue->id)
                ->get();

            foreach ($bookings as $booking) {
                $this->drawBooking($booking, $x, $colW, $top);
            }
        }

        $this->Line($right, $top - 7, $right, $bottom);
    }

    protected function drawHeading(string $title): void
    {
        $logo = $this->config['branding']['logo_path']
            ?: public_path('church/images/blacklogo.png');

        if ($logo && file_exists($logo)) {
            $this->Image($logo, 10, 0, 25, 25);
        }

        $this->SetFont('DejaVuCond', 'B', 22);
        $this->Text(40, 12, setting('general.church_name'));
        $this->SetFont('DejaVuCond', '', 16);
        $this->Text(40, 20, $title);
    }

    protected function drawGrid(int $rows, float $top, float $bottom, float $left, float $right): void
    {
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('DejaVuCond', 'B', 12);

        for ($i = 0; $i <= $rows; $i++) {
            $y = $top + $i * self::ROW_H;
            $this->Line($left, $y, $right, $y);

            if ($i < $rows) {
                $this->Text($left + 3, $y + 7, sprintf('%02dh00', self::FIRST_HOUR + $i));
            }
        }

        $this->Line($left, $top, $left, $bottom);
    }

    protected function drawBooking($booking, float $x, float $colW, float $gridTop): void
    {
        $startY = $this->timeToY(substr($booking->diarydatetime, 11, 5), $gridTop);
        $endY   = $this->timeToY(substr((string) $booking->endtime, 0, 5), $gridTop);

        if ($endY <= $startY) {
            return;
        }

        $this->Rect($x, $startY, $colW, $endY - $startY, 'DF');

        $label = $this->bookingLabel($booking);
        if ($label !== '') {
            $this->SetFont('DejaVuCond', '', 8);
            $this->SetXY($x, $startY + 1);
            $this->MultiCell($colW, 3, $label, 0, 'C');
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

        $name = $d->tenant ?? $d->groupname ?? $d->event ?? null;

        if (! $name) {
            return '';
        }

        return $booking->details ? "{$name} ({$booking->details})" : $name;
    }
}