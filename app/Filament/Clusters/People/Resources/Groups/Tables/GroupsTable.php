<?php

namespace App\Filament\Clusters\People\Resources\Groups\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('groupname')->label('Group')
                    ->searchable(),
                TextColumn::make('individual.fullname')->label('Leader'),
                IconColumn::make('grouptype')->label('Type')
                    ->icon(fn (string $state): string => match ($state) {
                        'admin' => 'heroicon-o-chart-bar',
                        'fellowship' => 'heroicon-o-user-group',
                        'service' => 'heroicon-o-wrench-screwdriver',
                    }),
            ])
            ->defaultSort('groupname')
            ->filters([
                SelectFilter::make('grouptype')->label('')
                    ->options([
                        'admin' => 'Admin',
                        'fellowship' => 'Fellowship',
                        'service' => 'Service'
                    ])
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
            ]);
    }
}
