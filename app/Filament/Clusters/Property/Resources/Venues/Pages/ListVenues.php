<?php

namespace App\Filament\Clusters\Property\Resources\Venues\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Clusters\Property\Resources\Venues\VenueResource;
use App\Filament\Widgets\ChurchVenuesWidget;
use Filament\Actions\Action;

class ListVenues extends ListRecords
{
    protected static string $resource = VenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Venue users')->url(fn (): string => route('filament.admin.property.resources.tenants.index')),
            CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array {
        return [
            ChurchVenuesWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }
}
