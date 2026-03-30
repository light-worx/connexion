<?php

namespace App\Filament\Clusters\People\Resources\Groups\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Clusters\People\Resources\Groups\GroupResource;

class EditGroup extends EditRecord
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Group SMS')->label('Group SMS')
                ->schema([
                    TextEntry::make('Credits')->state(function (){
                        /*$smss = new BulksmsService(setting('services.bulksms_clientid'), setting('services.bulksms_api_secret'));
                        return "Available credits: " . $smss->get_credits();*/
                    }),
                    Textarea::make('message')
                ])
                ->action(function (array $data) {
                    self::sendSMS($data);
                }),
            Action::make('Group email')->label('Group email')
                ->schema([
                    TextInput::make('subject'),
                    FileUpload::make('attachment')->preserveFilenames()->directory('attachments'),
                    MarkdownEditor::make('body')
                ])
                ->action(function (array $data) {
                    self::sendEmail($data);
                }),
            Action::make('Group report')->url(fn (): string => route('reports.group', ['id' => $this->record])),
            DeleteAction::make(),
        ];
    }
}
