<?php

namespace App\Filament\Clusters\People\Resources\Groups\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('groupname')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('meetingday')
                    ->numeric(),
                TimePicker::make('meetingtime'),
                TextInput::make('grouptype')
                    ->required()
                    ->default('service'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image(),
                Select::make('individual_id')
                    ->relationship('individual', 'title')
                    ->required(),
                Toggle::make('publish'),
            ]);
    }
}
