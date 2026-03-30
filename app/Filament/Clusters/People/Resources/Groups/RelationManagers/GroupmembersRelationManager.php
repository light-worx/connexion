<?php

namespace App\Filament\Clusters\People\Resources\Groups\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Groupmember;
use App\Models\Individual;

class GroupmembersRelationManager extends RelationManager
{
    protected static string $relationship = 'groupmembers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('individual_id')
                    ->label('Group member')
                    ->options(Individual::orderBy('surname')->get()->pluck('fullname', 'id'))
                    ->searchable(),
                Select::make('categories')->label('Service times (if applicable)')
                    ->visible(function ($livewire){
                        if ($livewire->ownerRecord->grouptype=="service"){
                            return true;
                        } else {
                            return false;
                        }
                    })
                    ->multiple()
                    ->options(fn () => array_combine(setting('services'),setting('services'))),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn (Groupmember $record): string => "{$record->firstname} {$record->surname}")
            ->columns([
                TextColumn::make('individual.fullname')->label('Name'),
                TextColumn::make('categories')->label('Service (if applicable)')
                    ->visible(function ($livewire){
                        if ($livewire->ownerRecord->grouptype=="service"){
                            return true;
                        } else {
                            return false;
                        }
                    }),
            ])
            ->emptyStateHeading('No group members')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
