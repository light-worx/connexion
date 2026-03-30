<?php

namespace App\Filament\Clusters\Website\Resources\Projects\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Website\Resources\Projects\ProjectResource;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;
}
