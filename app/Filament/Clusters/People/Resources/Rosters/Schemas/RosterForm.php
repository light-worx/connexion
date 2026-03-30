<?php

namespace App\Filament\Clusters\People\Resources\Rosters\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RosterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('roster')
                    ->required()
                    ->maxLength(255),
                Select::make('dayofweek')
                    ->label('Day of week')
                    ->required()
                    ->options([
                        'Sunday' => 'Sunday',
                        'Monday' => 'Monday',
                        'Tuesday' => 'Tuesday',
                        'Wednesday' => 'Wednesday',
                        'Thursday' => 'Thursday',
                        'Friday' => 'Friday',
                        'Saturday' => 'Saturday'
                    ])
                    ->live()
                    ->default('Sunday'),
                TextInput::make('message')
                    ->required()
                    ->maxLength(255),
                Select::make('sundayservice')->label('Sunday service')
                    ->hidden(fn (Get $get): bool => !($get('dayofweek') == "Sunday"))
                    ->options(fn () => array_combine(setting('services'),setting('services'))),
            ]);
    }
}
