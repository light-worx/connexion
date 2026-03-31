<?php

namespace App\Filament\Clusters\People\Resources\Households;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\People\PeopleCluster;
use App\Filament\Clusters\People\Resources\Households\Pages\CreateHousehold;
use App\Filament\Clusters\People\Resources\Households\Pages\EditHousehold;
use App\Filament\Clusters\People\Resources\Households\Pages\ListHouseholds;
use App\Filament\Clusters\People\Resources\Households\Schemas\HouseholdForm;
use App\Filament\Clusters\People\Resources\Households\Tables\HouseholdsTable;
use App\Models\Household;

class HouseholdResource extends Resource
{
    protected static ?string $model = Household::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = PeopleCluster::class;

    protected static ?string $recordTitleAttribute = 'addressee';

    public static function canAccess(): bool
    {
        return setting('people_module');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false; 
    }

    public static function form(Schema $schema): Schema
    {
        return HouseholdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HouseholdsTable::configure($table);
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
            'index' => ListHouseholds::route('/'),
            'create' => CreateHousehold::route('/create'),
            'edit' => EditHousehold::route('/{record}/edit'),
        ];
    }
}
