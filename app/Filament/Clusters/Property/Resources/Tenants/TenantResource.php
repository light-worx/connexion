<?php

namespace App\Filament\Clusters\Property\Resources\Tenants;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\Property\PropertyCluster;
use App\Filament\Clusters\Property\Resources\Tenants\Pages\CreateTenant;
use App\Filament\Clusters\Property\Resources\Tenants\Pages\EditTenant;
use App\Filament\Clusters\Property\Resources\Tenants\Pages\ListTenants;
use App\Filament\Clusters\Property\Resources\Tenants\Schemas\TenantForm;
use App\Filament\Clusters\Property\Resources\Tenants\Tables\TenantsTable;
use App\Models\Tenant;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $cluster = PropertyCluster::class;

    protected static ?string $recordTitleAttribute = 'tenant';

    public static function form(Schema $schema): Schema
    {
        return TenantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TenantsTable::configure($table);
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
            'index' => ListTenants::route('/'),
            'create' => CreateTenant::route('/create'),
            'edit' => EditTenant::route('/{record}/edit'),
        ];
    }
}
