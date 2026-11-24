<?php

namespace App\Filament\Clusters\Property\Resources\Venues\Pages;

use App\Filament\Clusters\Property\Resources\Venues\VenueResource;
use App\Filament\Widgets\ChurchCalendarWidget;
use Filament\Resources\Pages\ViewRecord;

class ViewVenue extends ViewRecord
{
    protected static string $resource = VenueResource::class;

    protected function getFooterWidgets(): array {
        return [
            ChurchCalendarWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }

}
