<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class People extends Cluster
{
    protected static ?int $navigationSort = -9;

    //protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function shouldRegisterNavigation(): bool
    {
        return self::canAccess();
    }

    public static function canAccess(): bool 
    { 
        return true;
    }

}
