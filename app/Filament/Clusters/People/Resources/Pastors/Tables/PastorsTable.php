<?php

namespace App\Filament\Clusters\People\Resources\Pastors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PastorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('individual.surname')->label('Surname')
                    ->sortable(),
                TextColumn::make('individual.firstname')->label('First name')
                    ->sortable(),
                IconColumn::make('active')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
            ]);
    }
}
