<?php

namespace App\Filament\Clusters\Website\Resources\People;

use App\Filament\Clusters\Website\Resources\People\Pages\CreatePerson;
use App\Filament\Clusters\Website\Resources\People\Pages\EditPerson;
use App\Filament\Clusters\Website\Resources\People\Pages\ListPeople;
use App\Filament\Clusters\Website\Resources\People\Schemas\PersonForm;
use App\Filament\Clusters\Website\Resources\People\Tables\PeopleTable;
use App\Filament\Clusters\Website\WebsiteCluster;
use App\Models\Person;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PersonResource extends Resource
{
    protected static ?string $model = Person::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = WebsiteCluster::class;

    protected static ?string $recordTitleAttribute = 'surname';

    public static function form(Schema $schema): Schema
    {
        return PersonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PeopleTable::configure($table);
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
            'index' => ListPeople::route('/'),
            'create' => CreatePerson::route('/create'),
            'edit' => EditPerson::route('/{record}/edit'),
        ];
    }
}
