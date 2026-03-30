<?php

namespace App\Filament\Clusters\Worship\Resources\Series\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\Worship\Resources\Series\SeriesResource;

class EditSeries extends EditRecord
{
    protected static string $resource = SeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
