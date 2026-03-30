<?php

namespace App\Filament\Clusters\People\Resources\Rosters\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RostergroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'rostergroups';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('group_id')
                    ->relationship('group', 'id')
                    ->required(),
                TextInput::make('maxpeople')
                    ->required()
                    ->numeric(),
                TextInput::make('extrainfo'),
                TextInput::make('extraoptions'),
                Toggle::make('editable'),
                TextInput::make('videos'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('rostergroup')
            ->columns([
                TextColumn::make('group.id')
                    ->searchable(),
                TextColumn::make('maxpeople')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('extrainfo')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('extraoptions')
                    ->searchable(),
                IconColumn::make('editable')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
