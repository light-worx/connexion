<?php

namespace App\Filament\Clusters\Property\Resources\Venues\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VenuesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('venue')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('width')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('length')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image'),
                TextColumn::make('publish')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('resource')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('colour')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
