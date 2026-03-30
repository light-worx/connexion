<?php

namespace App\Filament\Clusters\Website\Resources\Projects\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('project')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->maxLength(199),
                TextInput::make('slug')
                    ->required(),
                RichEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image(),
                Select::make('tags')
                    ->relationship('tags','name',modifyQueryUsing: fn (Builder $query) => $query->where('type','project'))
                    ->multiple()
                    ->createOptionForm([
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                    ->required(),
                                TextInput::make('type')
                                    ->default('project')
                                    ->readonly()
                                    ->required(),
                                TextInput::make('slug')
                                    ->required(),
                            ])
                    ]),
                Toggle::make('active'),
                Toggle::make('publish')->label('Publish on Hub website')
            ]);
    }
}
