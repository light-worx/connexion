<?php

namespace App\Filament\Clusters\Worship\Resources\Services;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\Worship\Resources\Services\Pages\CreateService;
use App\Filament\Clusters\Worship\Resources\Services\Pages\EditService;
use App\Filament\Clusters\Worship\Resources\Services\Pages\ListServices;
use App\Filament\Clusters\Worship\Resources\Services\Schemas\ServiceForm;
use App\Filament\Clusters\Worship\Resources\Services\Tables\ServicesTable;
use App\Filament\Clusters\Worship\WorshipCluster;
use App\Models\Service;

class ServiceResource extends Resource
{
    protected static ?int $navigationSort = 1;
    
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?string $cluster = WorshipCluster::class;

    protected static ?string $recordTitleAttribute = 'servicedate';

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
