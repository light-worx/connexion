<?php

namespace App\Filament\Clusters\People\Resources\Rosters\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Clusters\People\Resources\Rosters\RosterResource;

class ListRosters extends ListRecords
{
    protected static string $resource = RosterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
