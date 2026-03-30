<?php

namespace App\Filament\Clusters\People\Resources\Rosters;

use App\Models\Roster;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Clusters\People\PeopleCluster;
use App\Filament\Clusters\People\Resources\Rosters\Pages\CreateRoster;
use App\Filament\Clusters\People\Resources\Rosters\Pages\EditRoster;
use App\Filament\Clusters\People\Resources\Rosters\Pages\ListRosters;
use App\Filament\Clusters\People\Resources\Rosters\Schemas\RosterForm;
use App\Filament\Clusters\People\Resources\Rosters\Tables\RostersTable;

class RosterResource extends Resource
{
    protected static ?string $model = Roster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $cluster = PeopleCluster::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'roster';

    public static function form(Schema $schema): Schema
    {
        return RosterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RostersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RostergroupsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRosters::route('/'),
            'create' => CreateRoster::route('/create'),
            'edit' => EditRoster::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
