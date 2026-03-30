<?php

namespace App\Filament\Clusters\People\Resources\Pastors\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Clusters\People\Resources\Pastors\PastorResource;

class ListPastors extends ListRecords
{
    protected static string $resource = PastorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
