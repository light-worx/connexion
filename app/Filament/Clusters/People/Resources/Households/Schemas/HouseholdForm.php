<?php

namespace App\Filament\Clusters\People\Resources\Households\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HouseholdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('addressee')
                    ->required(),
                TextInput::make('address1'),
                TextInput::make('address2'),
                TextInput::make('address3'),
                TextInput::make('homephone')
                    ->tel(),
                TextInput::make('sortsurname'),
                TextInput::make('householdcell')
                    ->numeric(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
            ]);
    }
}
