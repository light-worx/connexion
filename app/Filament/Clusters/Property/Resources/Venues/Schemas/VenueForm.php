<?php

namespace App\Filament\Clusters\Property\Resources\Venues\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VenueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('venue')
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
                FileUpload::make('image')
                    ->hiddenOn('view')
                    ->image(),
                TextInput::make('publish')
                    ->hiddenOn('view')
                    ->required()
                    ->numeric(),
                TextInput::make('resource')
                    ->hiddenOn('view')
                    ->required()
                    ->numeric(),
                ColorPicker::make('colour')
                    ->hiddenOn('view'),
            ]);
    }
}
