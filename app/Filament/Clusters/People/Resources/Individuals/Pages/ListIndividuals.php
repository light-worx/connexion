<?php

namespace App\Filament\Clusters\People\Resources\Individuals\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Clusters\People\Resources\Individuals\IndividualResource;

class ListIndividuals extends ListRecords
{
    protected static string $resource = IndividualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
