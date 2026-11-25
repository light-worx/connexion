<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Diaryentry;
use App\Models\Event;
use App\Models\Group as GroupModel;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\Venue;
use Carbon\Carbon;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\Actions\EditAction;
use Guava\Calendar\Filament\CalendarWidget;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;

class ChurchCalendarWidget extends CalendarWidget
{
    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;

    protected bool $eventClickEnabled = true;

    protected int | string | array $columnSpan = 'full';

    protected bool $eventDragEnabled = true;

    protected bool $eventResizeEnabled = true;

    public ?Model $record = null;

    protected bool $dateSelectEnabled = true;
    
    protected static bool $isDiscovered = false;

    public function getEvents(FetchInfo $info): Collection|array
    {
        if ($this->record?->id) {
            return Diaryentry::query()
                ->where('venue_id', $this->record->id)
                ->get();
        }

        return Diaryentry::query()->get();
    }

    public function getResources(): Collection|array
    {
        return collect()
            ->push(...Venue::query()->where('resource',1)->get())
        ;
    }

    public function getEventContent(): null | string | array
    {
        return [
            Diaryentry::class => view('church::components.diaryentry'),
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            CreateAction::make('createDiaryentry')->label('Add a booking')
                ->modelLabel('Booking')
                ->icon('heroicon-o-calendar-days')
                ->using(function (array $data) {
                    // Make sure venue_id is always treated as an array
                    $venues = (array) $data['venue_id'];
                    foreach ($venues as $venueId) {
                        // Loop over repeats
                        for ($i = 0; $i <= $data['repeats']; $i++) {
                            $newtime = date(
                                'Y-m-d H:i',
                                strtotime($data['diarydatetime'] . ' + ' . $i * $data['interval'] . ' days')
                            );
                            Diaryentry::create([
                                'diarisable_id'   => $data['diarisable_id'],
                                'diarisable_type' => $data['diarisable_type'],
                                'venue_id'        => $venueId,
                                'details'         => $data['details'],
                                'diarydatetime'   => $newtime,
                                'endtime'         => $data['endtime'],
                            ]);
                        }
                    }
                })
        ];
    }

    public function getEventClickContextMenuActions(): array
    {
        return [
             EditAction::make('ctxEditDiaryentry')
                ->model(Diaryentry::class)
                ->modelLabel('Booking')
                ->icon('heroicon-o-pencil')
                ->using(function (Diaryentry $record, array $data) {
                    // If venue_id is an array, store only the first value
                    if (isset($data['venue_id']) && is_array($data['venue_id'])) {
                        $data['venue_id'] = $data['venue_id'][0] ?? null;
                    }
                    $record->update($data);
                })
                ->mountUsing(function (Form $form, Diaryentry $record) {
                    $data = $record->toArray();

                    // Wrap single venue_id in array so form fills correctly
                    if (isset($data['venue_id']) && !is_array($data['venue_id'])) {
                        $data['venue_id'] = [$data['venue_id']];
                    }
                    $form->fill($data);
            }),
            $this->deleteAction(),
        ];
    }

    public function getDateSelectContextMenuActions(): array
    {
        return [
            CreateAction::make('ctxCreateDiaryentry')
                ->model(Diaryentry::class)->modelLabel('Booking')
                ->using(function (array $data) {
                    // Ensure venue_id is always treated as an array
                    $venues = (array) $data['venue_id'];
                    $count = 0;
                    foreach ($venues as $venueId) {
                        for ($i = 0; $i <= $data['repeats']; $i++) {
                            $newtime = date(
                                'Y-m-d H:i',
                                strtotime($data['diarydatetime'] . ' + ' . $i * $data['interval'] . ' days')
                            );
                            Diaryentry::create([
                                'diarisable_id'   => $data['diarisable_id'],
                                'diarisable_type' => $data['diarisable_type'] ?? 'tenant',
                                'venue_id'        => $venueId,
                                'details'         => $data['details'],
                                'calendar'        => $data['calendar'] ?? 0,
                                'diarydatetime'   => $newtime,
                                'endtime'         => $data['endtime'],
                            ]);
                            $count++;
                        }
                    }
                    Notification::make()
                        ->title("{$count} booking(s) created successfully")
                        ->success()
                        ->send();
                })
                ->mountUsing(function (Form $form, array $arguments) {
                    $getvenue = data_get($arguments, 'resource');
                    if ($getvenue) {
                        $venues = [$getvenue['id']]; // Wrap in array
                    } else {
                        $venues = $this->record ? [$this->record->id] : [];
                    }
                    $tenant = data_get($arguments, 'diarisable_id');
                    $utype = data_get($arguments, 'diarisable_type');
                    if (!$utype){
                        $utype="tenant";
                    }
                    $diarydatetime = data_get($arguments, 'startStr');
                    $endtime = data_get($arguments, 'endStr');
                    if ($diarydatetime) {
                        $form->fill([
                            'diarisable_id' => $tenant,
                            'diarisable_type' => $utype,
                            'diarydatetime' => Carbon::make($diarydatetime),
                            'endtime' => Carbon::make($endtime),
                            'venue_id' => $venues
                        ]);
                    }
                }),
        ];
    }

    public function getTimeClickContextMenuActions(): array
    {
        return [
            CreateAction::make('ctxCreateDiaryentry')
                ->model(Diaryentry::class)->modelLabel('Booking')
                ->mountUsing(function (Form $form, array $arguments) {
                    $date = data_get($arguments, 'dateStr');
                    if ($date) {
                        $form->fill([
                            'diarydatetime' => Carbon::make($date),
                            'endtime' => Carbon::make($date),
                        ]);
                    }
                }),
        ];
    }

    public function getSchema(string $name): ?Schema
    {
        return match ($name) {
            Diaryentry::class => Schema::make()
                ->components([
                    Group::make([
                        Select::make('diarisable_id')
                            ->label('Venue user')
                            ->options(function (Get $get) {
                                if ($get('diarisable_type') == 'group') {
                                    return GroupModel::orderBy('groupname')->get()->pluck('groupname', 'id');
                                } elseif ($get('diarisable_type') == 'event') {
                                    return Event::orderBy('event')->get()->pluck('event', 'id');
                                } elseif ($get('diarisable_type') == 'course') {
                                    //return Course::orderBy('course')->get()->pluck('course', 'id');
                                } elseif ($get('diarisable_type') == 'project') {
                                    return Project::orderBy('project')->get()->pluck('project', 'id');
                                } else {
                                    return Tenant::orderBy('tenant')->get()->pluck('tenant', 'id');
                                }
                            })
                            ->searchable()
                            ->required(),
                        Select::make('diarisable_type')
                            ->label('User type')
                            ->options([
                                'tenant' => 'External group',
                                'course' => setting('general.church_abbreviation') . ' course',
                                'event' => setting('general.church_abbreviation') . ' event',
                                'group' => setting('general.church_abbreviation') . ' group',
                                'project' => 'Mission project',
                            ])
                            ->selectablePlaceholder(false)
                            ->default('tenant'),
                    ])->columns(),
                    Select::make('venue_id')
                        ->label('Venue')
                        ->options(Venue::orderBy('venue')->get()->pluck('venue', 'id'))
                        ->multiple()
                        ->searchable()
                        ->default([$this->record->id]),
                    Textarea::make('details')
                        ->rows(5),
                    Group::make([
                        TextInput::make('repeats')
                            ->label('Number of times to repeat')
                            ->default(0)
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(52),
                        Select::make('interval')
                            ->options([
                                1 => 'daily',
                                7 => 'weekly',
                                14 => 'fortnightly',
                            ])
                            ->default(7)
                            ->label('How often to repeat'),
                        Checkbox::make('calendar')
                            ->label('Add to church calendar'),
                    ])->columns(),
                    Group::make([
                        DateTimePicker::make('diarydatetime')
                            ->label('Start time and date')
                            ->native(true)
                            ->default(function () {
                                return date('Y-m-d H:00', strtotime('+2 hours'));
                            })
                            ->seconds(false)
                            ->displayFormat('Y-m-d H:i')
                            ->format('Y-m-d H:i')
                            ->required(),
                        TimePicker::make('endtime')
                            ->label('End time')
                            ->default(function () {
                                return date('H:00', strtotime('+3 hours'));
                            })
                            ->native(true)
                            ->seconds(false)
                            ->required(),
                    ])->columns(),
                ]),
            default => null,
        };
    }

    public function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        if ($event instanceof Diaryentry) {

            // Get the new start and end times
            $startsAt = $info->event->getStart();
            $endsAt = $info->event->getEnd();

            // Update the diary entry
            $event->update([
                'diarydatetime' => $startsAt,
                'endtime' => $endsAt,
            ]);

            Notification::make()
                ->title('Event date moved!')
                ->success()
                ->send();

            return true;
        }

        return false;
    }

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        if ($event instanceof Diaryentry) {

            $endsAt = $info->event->getEnd();

            $event->update([
                'endtime' => $endsAt->format('H:i'),
            ]);

            Notification::make()
                ->title('Event duration changed!')
                ->success()
                ->send();

            return true;
        }

        Notification::make()
            ->title('Duration of this event cannot be changed!')
            ->danger()
            ->send();

        return false;
    }

    public function authorize($ability, $arguments = [])
    {
        return true;
    }

    public function getOptions(): array
    {
        return [
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '21:00:00',
            'headerToolbar' => [
                'start' => 'title',
                'center' => 'dayGridMonth,timeGridWeek,timeGridDay',
                'end' => 'today prev,next',
            ]
        ];
    }

}