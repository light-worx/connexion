<?php

namespace App\Filament\Clusters\People\Resources\Individuals\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class IndividualForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->columns(2)
                    ->tabs([
                        Tab::make('Personal')
                            ->schema([
                                Select::make('title')
                                    ->selectablePlaceholder(false)
                                    ->options([
                                        '' => '',
                                        'Mr' => 'Mr',
                                        'Mrs' => 'Mrs',
                                        'Ms' => 'Ms',
                                        'Dr' => 'Dr',
                                        'Rev' => 'Rev',
                                    ]),
                                TextInput::make('surname')
                                    ->required(),
                                TextInput::make('firstname')
                                    ->label('First name')
                                    ->required(),
                                Grid::make(1) // keeps it compact (single column block)
                                    ->schema([
                                        Grid::make(3) // internal layout
                                            ->extraAttributes(['class' => 'max-w-md']) // constrain width
                                            ->schema([
                                                Select::make('birth_month')
                                                    ->label('Birthday (month)')
                                                    ->options([
                                                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar',
                                                        4 => 'Apr', 5 => 'May', 6 => 'Jun',
                                                        7 => 'Jul', 8 => 'Aug', 9 => 'Sep',
                                                        10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
                                                    ])
                                                    ->placeholder('Month')
                                                    ->live() // triggers reactivity
                                                    ->afterStateUpdated(fn ($set) => $set('birth_day', null)),
                                                Select::make('birth_day')
                                                    ->label('Birthday (day)')
                                                    ->placeholder('Day')
                                                    ->options(function (Get $get) {
                                                        $month = (int) $get('birth_month');

                                                        if (!$month) {
                                                            return [];
                                                        }

                                                        $days = match ($month) {
                                                            2 => 29, // allow Feb 29 (no year validation here)
                                                            4, 6, 9, 11 => 30,
                                                            default => 31,
                                                        };

                                                        return array_combine(range(1, $days), range(1, $days));
                                                    }),
                                                TextInput::make('birth_year')
                                                    ->label('Birthday (year)')
                                                    ->numeric()
                                                    ->placeholder('Year')
                                                    ->minValue(1900)
                                                    ->maxValue(now()->year)
                                                    ->nullable(), // allows blank
                                            ]),
                                    ]),
                                Select::make('sex')
                                    ->selectablePlaceholder(false)
                                    ->options([
                                        '' => '',
                                        'Female' => 'Female',
                                        'Male' => 'Male'
                                    ]),
                                FileUpload::make('image')
                                    ->image(),
                            ]),
                        Tab::make('Contact')
                            ->schema([
                                Select::make('household_id')
                                    ->required()
                                    ->label('Household')
                                    ->relationship('household', 'addressee')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('addressee')->required(),
                                        TextInput::make('address'),
                                        TextInput::make('phone'),
                                    ]),
                                TextInput::make('email')
                                    ->label('Email address')
                                    ->email(),
                                TextInput::make('officephone')
                                    ->tel(),
                                TextInput::make('cellphone')
                                    ->tel(),
                            ]),
                        Tab::make('Pastoral')
                            ->schema([
                                TextInput::make('groupleader')
                                    ->label('Group leader')
                                    ->numeric(),
                                TextInput::make('welcome_email')
                                    ->email(),                
                            ]),
                        Tab::make('Groups')
                            ->schema([
                            ]),
                        Tab::make('Admin')
                            ->schema([
                                TextInput::make('memberstatus'),
                                TextInput::make('giving'),
                                Textarea::make('notes')
                                    ->columnSpanFull(),            
                                TextInput::make('user_id')
                                    ->numeric(),
                                TextInput::make('uid'),
                                TextInput::make('app'),
                                TextInput::make('online')
                                    ->numeric(),
                                TextInput::make('nametag_exclude')
                                    ->numeric()                 
                            ])
                    ])
            ]);
    }
}
