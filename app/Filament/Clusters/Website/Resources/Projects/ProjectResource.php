<?php

namespace App\Filament\Clusters\Website\Resources\Projects;

use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\Website\Resources\Projects\Pages\CreateProject;
use App\Filament\Clusters\Website\Resources\Projects\Pages\EditProject;
use App\Filament\Clusters\Website\Resources\Projects\Pages\ListProjects;
use App\Filament\Clusters\Website\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Clusters\Website\Resources\Projects\Tables\ProjectsTable;
use App\Filament\Clusters\Website\WebsiteCluster;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $cluster = WebsiteCluster::class;

    protected static ?string $recordTitleAttribute = 'project';

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
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
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
