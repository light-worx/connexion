<?php

namespace App\Filament\Clusters\Worship\Resources\Services\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Worship\Resources\Services\ServiceResource;
use App\Services\OrderOfServiceService;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function afterCreate(): void
    {
        app(OrderOfServiceService::class)->populate($this->record);
    }
}
