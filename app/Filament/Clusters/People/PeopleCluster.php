<?php

namespace App\Filament\Clusters\People;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class PeopleCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;
}
