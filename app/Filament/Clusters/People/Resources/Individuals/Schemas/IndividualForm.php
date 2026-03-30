<?php

namespace App\Filament\Clusters\People\Resources\Individuals\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class IndividualForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title'),
                TextInput::make('surname')
                    ->required(),
                TextInput::make('firstname')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('birthdate'),
                TextInput::make('sex'),
                FileUpload::make('image')
                    ->image(),
                TextInput::make('memberstatus'),
                Select::make('household_id')
                    ->relationship('household', 'id')
                    ->required(),
                TextInput::make('groupleader')
                    ->numeric(),
                TextInput::make('giving'),
                TextInput::make('officephone')
                    ->tel(),
                TextInput::make('cellphone')
                    ->tel(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('welcome_email')
                    ->email(),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('uid'),
                TextInput::make('app'),
                TextInput::make('online')
                    ->numeric(),
                TextInput::make('nametag_exclude')
                    ->numeric(),
            ]);
    }
}
