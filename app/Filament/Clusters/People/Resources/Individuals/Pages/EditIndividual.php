<?php

namespace App\Filament\Clusters\People\Resources\Individuals\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\People\Resources\Individuals\IndividualResource;

class EditIndividual extends EditRecord
{
    protected static string $resource = IndividualResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
