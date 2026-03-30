<?php

namespace App\Filament\Clusters\Worship\Resources\WeekdayServices;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\Worship\Resources\WeekdayServices\Pages\CreateWeekdayService;
use App\Filament\Clusters\Worship\Resources\WeekdayServices\Pages\EditWeekdayService;
use App\Filament\Clusters\Worship\Resources\WeekdayServices\Pages\ListWeekdayServices;
use App\Filament\Clusters\Worship\Resources\WeekdayServices\Schemas\WeekdayServiceForm;
use App\Filament\Clusters\Worship\Resources\WeekdayServices\Tables\WeekdayServicesTable;
use App\Filament\Clusters\Worship\WorshipCluster;
use App\Models\WeekdayService;

class WeekdayServiceResource extends Resource
{
    protected static ?string $model = WeekdayService::class;

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDateRange;

    protected static ?string $cluster = WorshipCluster::class;

    public static function form(Schema $schema): Schema
    {
        return WeekdayServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeekdayServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWeekdayServices::route('/'),
            'create' => CreateWeekdayService::route('/create'),
            'edit' => EditWeekdayService::route('/{record}/edit'),
        ];
    }
}
