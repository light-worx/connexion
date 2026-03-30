<?php

namespace App\Filament\Clusters\Property\Resources\Venues\Schemas;

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
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('width')
                    ->required()
                    ->numeric(),
                TextInput::make('length')
                    ->required()
                    ->numeric(),
                FileUpload::make('image')
                    ->image(),
                TextInput::make('publish')
                    ->required()
                    ->numeric(),
                TextInput::make('resource')
                    ->required()
                    ->numeric(),
                TextInput::make('colour'),
            ]);
    }
}
