<?php

namespace App\Filament\Clusters\Worship\Resources\Prayers;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\Worship\Resources\Prayers\Pages\CreatePrayer;
use App\Filament\Clusters\Worship\Resources\Prayers\Pages\EditPrayer;
use App\Filament\Clusters\Worship\Resources\Prayers\Pages\ListPrayers;
use App\Filament\Clusters\Worship\Resources\Prayers\Schemas\PrayerForm;
use App\Filament\Clusters\Worship\Resources\Prayers\Tables\PrayersTable;
use App\Filament\Clusters\Worship\WorshipCluster;
use App\Models\Prayer;
use UnitEnum;

class PrayerResource extends Resource
{
    protected static ?string $model = Prayer::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'liturgy';

    protected static string | UnitEnum | null $navigationGroup = 'Service planning';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $cluster = WorshipCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canAccess(): bool
    {
        return setting('worship_module');
    }

    public static function form(Schema $schema): Schema
    {
        return PrayerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrayersTable::configure($table);
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
            'index' => ListPrayers::route('/'),
            'create' => CreatePrayer::route('/create'),
            'edit' => EditPrayer::route('/{record}/edit'),
        ];
    }
}
