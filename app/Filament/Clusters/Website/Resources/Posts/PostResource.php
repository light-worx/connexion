<?php

namespace App\Filament\Clusters\Website\Resources\Posts;

use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Clusters\Website\Resources\Posts\Pages\CreatePost;
use App\Filament\Clusters\Website\Resources\Posts\Pages\EditPost;
use App\Filament\Clusters\Website\Resources\Posts\Pages\ListPosts;
use App\Filament\Clusters\Website\Resources\Posts\Schemas\PostForm;
use App\Filament\Clusters\Website\Resources\Posts\Tables\PostsTable;
use App\Filament\Clusters\Website\WebsiteCluster;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?string $cluster = WebsiteCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canAccess(): bool
    {
        return setting('website_module');
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
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
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
