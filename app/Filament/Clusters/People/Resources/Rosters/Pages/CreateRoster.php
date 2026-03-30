<?php

namespace App\Filament\Clusters\People\Resources\Rosters\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\People\Resources\Rosters\RosterResource;

class CreateRoster extends CreateRecord
{
    protected static string $resource = RosterResource::class;
}
