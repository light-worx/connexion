<?php

namespace App\Filament\Clusters\People\Resources\Individuals\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\People\Resources\Individuals\IndividualResource;

class CreateIndividual extends CreateRecord
{
    protected static string $resource = IndividualResource::class;
}
