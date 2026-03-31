<?php

namespace App\Filament\Clusters\Property;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class PropertyCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function canAccess(): bool
    {
        return setting('property_module');
    }
}
