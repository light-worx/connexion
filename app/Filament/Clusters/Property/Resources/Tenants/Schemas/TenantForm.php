<?php

namespace App\Filament\Clusters\Property\Resources\Tenants\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('contact_firstname'),
                TextInput::make('contact_surname'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('active')
                    ->numeric(),
                TextInput::make('publish')
                    ->numeric(),
            ]);
    }
}
