<?php

namespace App\Filament\Clusters\People\Resources\Individuals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IndividualsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('surname')
                    ->searchable(),
                TextColumn::make('firstname')
                    ->label('First name')
                    ->searchable(),
                TextColumn::make('household.addressee')
                    ->searchable(),
                TextColumn::make('cellphone')
                    ->searchable()
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
