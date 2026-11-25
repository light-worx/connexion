<?php

namespace App\Filament\Clusters\Property\Resources\Venues\Pages;

use App\Filament\Clusters\Property\Resources\Venues\VenueResource;
use App\Filament\Widgets\ChurchCalendarWidget;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

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

    protected function getHeaderActions(): array
{
    return [
        Action::make('weekReport')
            ->label('Week report')
            ->icon('heroicon-o-document-text')
            ->schema([
                DatePicker::make('reportdate')
                    ->label('Report date')
                    ->format('Y-m-d')
                    ->displayFormat('Y-m-d')
                    ->weekStartsOnMonday()
                    ->default(now())
                    ->required(),
                Select::make('header')
                    ->options(['church' => 'Church', 'hub' => 'Community Hub'])
                    ->default('church')
                    ->required(),
            ])
            ->action(function (array $data): void {
                $url = route('reports.venue', [
                    'id'         => $this->record->id,
                    'reportdate' => $data['reportdate'],
                    'header'     => $data['header'],
                ]);

                $this->js('window.open(' . json_encode($url) . ', "_blank")');
            })
    ];
}

}
