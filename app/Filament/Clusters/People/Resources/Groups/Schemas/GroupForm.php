<?php

namespace App\Filament\Clusters\People\Resources\Groups\Schemas;

use App\Models\Individual;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('groupname')
                    ->required()->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required(),
                Select::make('meetingday')
                    ->options([
                        '0' => 'Sunday',
                        '1' => 'Monday',
                        '2' => 'Tuesday',
                        '3' => 'Wednesday',
                        '4' => 'Thursday',
                        '5' => 'Friday',
                        '6' => 'Saturday'
                    ])
                    ->label('Meeting day'),
                TimePicker::make('meetingtime'),
                Select::make('grouptype')
                    ->required()
                    ->options([
                        'admin' => 'Admin',
                        'fellowship' => 'Fellowship',
                        'service' => 'Service',
                    ])
                    ->default('service')
                    ->label('Group type'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image(),
                Select::make('individual_id')
                    ->label('Leader')
                    ->options(Individual::orderBy('firstname')->get()->pluck('fullname', 'id'))
                    ->searchable(),
                Toggle::make('publish'),
            ]);
    }
}
