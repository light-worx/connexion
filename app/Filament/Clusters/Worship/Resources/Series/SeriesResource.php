<?php

namespace App\Filament\Clusters\Worship\Resources\Series;

use App\Models\Series;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\Worship\Resources\Series\Pages\CreateSeries;
use App\Filament\Clusters\Worship\Resources\Series\Pages\EditSeries;
use App\Filament\Clusters\Worship\Resources\Series\Pages\ListSeries;
use App\Filament\Clusters\Worship\Resources\Series\Schemas\SeriesForm;
use App\Filament\Clusters\Worship\Resources\Series\Tables\SeriesTable;
use App\Filament\Clusters\Worship\WorshipCluster;
use UnitEnum;

class SeriesResource extends Resource
{
    protected static ?string $model = Series::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string | UnitEnum | null $navigationGroup = 'Admin';

    protected static ?string $cluster = WorshipCluster::class;

    protected static ?string $recordTitleAttribute = 'series';

    public static function canAccess(): bool
    {
        return setting('worship_module');
    }

    public static function form(Schema $schema): Schema
    {
        return SeriesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeriesTable::configure($table);
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
            'index' => ListSeries::route('/'),
            'create' => CreateSeries::route('/create'),
            'edit' => EditSeries::route('/{record}/edit'),
        ];
    }
}
