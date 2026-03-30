<?php

namespace App\Filament\Clusters\People\Resources\Pastors\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\People\Resources\Pastors\PastorResource;

class EditPastor extends EditRecord
{
    protected static string $resource = PastorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
