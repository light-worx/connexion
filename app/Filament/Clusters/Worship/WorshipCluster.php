<?php

namespace App\Filament\Clusters\Worship;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class WorshipCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::MusicalNote;

    public static function canAccess(): bool
    {
        return setting('worship_module');
    }
}
