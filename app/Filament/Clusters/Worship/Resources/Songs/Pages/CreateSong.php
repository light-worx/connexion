<?php

namespace App\Filament\Clusters\Worship\Resources\Songs\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Worship\Resources\Songs\SongResource;

class CreateSong extends CreateRecord
{
    protected static string $resource = SongResource::class;
}
