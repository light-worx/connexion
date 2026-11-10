<?php

namespace App\Filament\Clusters\Worship\Resources\Prayers\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PrayerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('copyright'),
                Select::make('tags')
                        ->relationship('tags','name',modifyQueryUsing: fn (Builder $query) => $query->where('type','liturgy'))
                        ->multiple()
                        ->preload()
                        ->createOptionForm([
                            Grid::make()
                                ->columns(2)
                                ->schema([
                                    TextInput::make('name')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                        ->required(),
                                    TextInput::make('type')
                                        ->default('liturgy')
                                        ->readonly()
                                        ->required(),
                                    TextInput::make('slug')
                                        ->required(),
                                ])
                        ]),
                RichEditor::make('words')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
