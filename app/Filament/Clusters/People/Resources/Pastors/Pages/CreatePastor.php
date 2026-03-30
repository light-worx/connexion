<?php

namespace App\Filament\Clusters\People\Resources\Pastors\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\People\Resources\Pastors\PastorResource;

class CreatePastor extends CreateRecord
{
    protected static string $resource = PastorResource::class;
}
