<?php

namespace App\Filament\Clusters\Property\Resources\Tenants\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Property\Resources\Tenants\TenantResource;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;
}
