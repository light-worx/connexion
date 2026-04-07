<?php

namespace App\Filament\Clusters\People\Resources\Individuals\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

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
                                Grid::make(1)
                                    ->schema([
                                        Select::make('household_id')
                                            ->required()
                                            ->label('Household')
                                            ->relationship('household', 'surname')
                                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                                            ->searchable()
                                            ->preload()
                                            ->createOptionForm(function (Get $get){
                                                return [
                                                TextInput::make('address1'),
                                                TextInput::make('address2'),
                                                TextInput::make('address3'),
                                                TextInput::make('homephone'),
                                                TextInput::make('surname')
                                                    ->default($get('surname'))
                                                    ->required(),
                                            ];}),
                                        Section::make('Household')
                                            ->relationship('household')
                                            ->schema([
                                                TextInput::make('address1')->label('Address 1'),
                                                TextInput::make('address2')->label('Address 2'),
                                                TextInput::make('address3')->label('Address 3'),
                                                TextInput::make('homephone')->label('Phone'),
                                                TextInput::make('surname')
                                                    ->label('Surname (used for sorting and to describe household)')
                                                    ->disabled(fn ($get) => false),
                                            ])
                                            ->visible(fn ($get) => $get('household_id') !== null),
                                    ]),
                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('email')
                                            ->label('Email address')
                                            ->prefixIcon('heroicon-m-envelope')
                                            ->email(),                                            
                                        PhoneInput::make('officephone')
                                            ->defaultCountry('ZA')
                                            ->strictMode(true)
                                            ->placeholder('031 123 4567')
                                            ->i18n(["searchPlaceholder" => ''])
                                            ->label('Office landline'),
                                        PhoneInput::make('cellphone')
                                            ->placeholder('082 123 4567')
                                            ->defaultCountry('ZA')
                                            ->strictMode(true)
                                            ->i18n(["searchPlaceholder" => ''])
                                    ]),
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
