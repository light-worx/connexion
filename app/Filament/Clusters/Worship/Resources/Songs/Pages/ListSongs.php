<?php

namespace App\Filament\Clusters\Worship\Resources\Songs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Clusters\Worship\Resources\Songs\SongResource;

class ListSongs extends ListRecords
{
    protected static string $resource = SongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
