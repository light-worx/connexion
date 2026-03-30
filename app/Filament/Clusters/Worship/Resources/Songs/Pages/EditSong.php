<?php

namespace App\Filament\Clusters\Worship\Resources\Songs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\Worship\Resources\Songs\SongResource;

class EditSong extends EditRecord
{
    protected static string $resource = SongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
