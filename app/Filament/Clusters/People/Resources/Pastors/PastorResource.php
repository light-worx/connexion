<?php

namespace App\Filament\Clusters\People\Resources\Pastors;

use App\Models\Pastor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\People\PeopleCluster;
use App\Filament\Clusters\People\Resources\Pastors\Pages\CreatePastor;
use App\Filament\Clusters\People\Resources\Pastors\Pages\EditPastor;
use App\Filament\Clusters\People\Resources\Pastors\Pages\ListPastors;
use App\Filament\Clusters\People\Resources\Pastors\Schemas\PastorForm;
use App\Filament\Clusters\People\Resources\Pastors\Tables\PastorsTable;

class PastorResource extends Resource
{
    protected static ?string $model = Pastor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $cluster = PeopleCluster::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Pastoral carer';

    protected static ?string $recordTitleAttribute = 'individual_id';

    public static function form(Schema $schema): Schema
    {
        return PastorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PastorsTable::configure($table);
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
            'index' => ListPastors::route('/'),
            'create' => CreatePastor::route('/create'),
            'edit' => EditPastor::route('/{record}/edit'),
        ];
    }
}
