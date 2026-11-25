<?php

namespace App\Filament\Clusters\Property\Resources\Venues\Pages;

use App\Filament\Clusters\Property\Resources\Venues\VenueResource;
use App\Filament\Widgets\ChurchVenuesWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListVenues extends ListRecords
{
    protected static string $resource = VenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Venue users')
                ->url(fn (): string => route('filament.admin.property.resources.tenants.index')),

            Action::make('report')
                ->label('Day report')
                ->icon('heroicon-o-document-text')
                ->schema([
                    DatePicker::make('reportdate')
                        ->label('Report date')
                        ->format('Y-m-d')
                        ->displayFormat('Y-m-d')
                        ->weekStartsOnMonday()
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $url = route('reports.allvenues', ['reportdate' => $data['reportdate']]);

                    $this->js('window.open(' . json_encode($url) . ', "_blank")');
                }),

            CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [ChurchVenuesWidget::class];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }
}