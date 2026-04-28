<?php

namespace App\Filament\Clusters\Website\Resources\People\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('firstname')
                    ->required(),
                TextInput::make('surname')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('bio')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image(),
                TextInput::make('role')
                    ->required(),
                Toggle::make('active'),
                TextInput::make('external_id')
                    ->numeric(),
            ]);
    }
}
