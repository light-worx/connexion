<?php

namespace App\Filament\Clusters\Property\Resources\Venues;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\Property\PropertyCluster;
use App\Filament\Clusters\Property\Resources\Venues\Pages\ViewVenue;
use App\Filament\Clusters\Property\Resources\Venues\Pages\CreateVenue;
use App\Filament\Clusters\Property\Resources\Venues\Pages\EditVenue;
use App\Filament\Clusters\Property\Resources\Venues\Pages\ListVenues;
use App\Filament\Clusters\Property\Resources\Venues\Schemas\VenueForm;
use App\Filament\Clusters\Property\Resources\Venues\Tables\VenuesTable;
use App\Filament\Widgets\ChurchCalendarWidget;
use App\Filament\Widgets\ChurchVenuesWidget;
use App\Models\Venue;
use Override;

class VenueResource extends Resource
{
    protected static ?string $model = Venue::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?string $cluster = PropertyCluster::class;

    protected static ?string $recordTitleAttribute = 'venue';

    public static function canAccess(): bool
    {
        return setting('property_module');
    }

    public static function form(Schema $schema): Schema
    {
        return VenueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VenuesTable::configure($table);
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
            'index' => ListVenues::route('/'),
            'create' => CreateVenue::route('/create'),
            'view' => ViewVenue::route('/{record}'),
            'edit' => EditVenue::route('/{record}/edit'),
        ];
    }
}
