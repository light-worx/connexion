<?php

namespace App\Filament\Clusters\Worship\Resources\Prayers\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Clusters\Worship\Resources\Prayers\PrayerResource;

class CreatePrayer extends CreateRecord
{
    protected static string $resource = PrayerResource::class;
}
