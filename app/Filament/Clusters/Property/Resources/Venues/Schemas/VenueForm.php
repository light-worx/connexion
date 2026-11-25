<?php

namespace App\Filament\Clusters\Property\Resources\Venues\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('venue')
                    ->required()->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->hiddenOn('view')
                    ->required(),
                TextInput::make('slug')
                    ->hiddenOn('view')
                    ->required(),
                Textarea::make('description')
                    ->hiddenOn('view')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('width')
                    ->hiddenOn('view')
                    ->required()
                    ->numeric(),
                TextInput::make('length')
                    ->hiddenOn('view')
                    ->required()
                    ->numeric(),
                Toggle::make('publish')
                    ->hiddenOn('view')
                    ->required(),
                Toggle::make('resource')
                    ->label('Show in resources view')
                    ->hiddenOn('view')
                    ->required(),
                ColorPicker::make('colour')
                    ->hiddenOn('view'),
                FileUpload::make('image')
                    ->hiddenOn('view')
                    ->image(),
            ]);
    }
}
