<?php

namespace App\Filament\Clusters\People\Resources\Groups\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\People\Resources\Groups\GroupResource;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;
}
