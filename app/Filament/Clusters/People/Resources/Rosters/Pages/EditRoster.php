<?php

namespace App\Filament\Clusters\People\Resources\Rosters\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\People\Resources\Rosters\RosterResource;

class EditRoster extends EditRecord
{
    protected static string $resource = RosterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
