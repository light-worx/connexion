<?php

namespace App\Filament\Clusters\People\Resources\Households\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\People\Resources\Households\HouseholdResource;

class EditHousehold extends EditRecord
{
    protected static string $resource = HouseholdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
