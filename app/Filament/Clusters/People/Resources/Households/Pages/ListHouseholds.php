<?php

namespace App\Filament\Clusters\People\Resources\Households\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Clusters\People\Resources\Households\HouseholdResource;

class ListHouseholds extends ListRecords
{
    protected static string $resource = HouseholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
