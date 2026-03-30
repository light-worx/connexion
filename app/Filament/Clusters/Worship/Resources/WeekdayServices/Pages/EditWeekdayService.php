<?php

namespace App\Filament\Clusters\Worship\Resources\WeekdayServices\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\Worship\Resources\WeekdayServices\WeekdayServiceResource;

class EditWeekdayService extends EditRecord
{
    protected static string $resource = WeekdayServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
