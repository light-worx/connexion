<?php

namespace App\Filament\Widgets;

use App\Models\Diaryentry;
use App\Models\Event;
use App\Models\Group as GroupModel;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\Venue;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Guava\Calendar\Contracts\ContextualInfo;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\Actions\EditAction;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ChurchVenuesWidget extends CalendarWidget
{
    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;

    protected int | string | array $columnSpan = 'full';

    protected bool $eventClickEnabled = true;

    protected static bool $isDiscovered = false;

    protected bool $eventDragEnabled = true;

    public ?Model $record = null;

    protected bool $eventResizeEnabled = true;

    protected bool $dateSelectEnabled = true;

    public function getEvents(FetchInfo $info): Collection|array
    {
        $past = date('Y-m-d', strtotime('- 1 week'));
        $future = date('Y-m-d', strtotime('+ 1 week'));

        return Diaryentry::query()
            ->where('diarydatetime', '>', $past)
            ->where('diarydatetime', '<', $future)
            ->get();
    }

    public function getResources(): Collection|array
    {
        return collect()
            ->push(...Venue::query()->where('resource', 1)->get());
    }

    public function getEventContent(): null | string | array
    {
        return [
            Diaryentry::class => view('church::components.diaryentry'),
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
                    // The form allows multiple venues, but an existing booking has one
                    if (isset($data['venue_id']) && is_array($data['venue_id'])) {
                        $data['venue_id'] = $data['venue_id'][0] ?? null;
                    }
                    $record->update($data);
                })
                ->mountUsing(function (Schema $schema, Diaryentry $record) {
                    $data = $record->toArray();

                    // Wrap single venue_id in an array so the multiple Select fills correctly
                    if (isset($data['venue_id']) && ! is_array($data['venue_id'])) {
                        $data['venue_id'] = [$data['venue_id']];
                    }
                    $schema->fill($data);
                }),
            $this->deleteAction(),
        ];
    }

    public function getDateSelectContextMenuActions(): array
    {
        return [
            CreateAction::make('ctxCreateDiaryentry')
                ->model(Diaryentry::class)
                ->modelLabel('Booking')
                ->using(fn (array $data) => $this->createBookings($data))
                ->mountUsing(function (Schema $schema, ?ContextualInfo $info, array $arguments) {
                    $this->prefillBooking($schema, $info, $arguments);
                }),
        ];
    }

    public function getTimeClickContextMenuActions(): array
    {
        return [
            CreateAction::make('ctxCreateDiaryentryAtTime')
                ->model(Diaryentry::class)
                ->modelLabel('Booking')
                ->using(fn (array $data) => $this->createBookings($data))
                ->mountUsing(function (Schema $schema, ?ContextualInfo $info, array $arguments) {
                    $this->prefillBooking($schema, $info, $arguments);
                }),
        ];
    }

    /**
     * Creates the booking(s), repeating per venue as requested.
     * Shared by both create actions.
     */
    protected function createBookings(array $data): void
    {
        $venues = (array) $data['venue_id'];
        $count = 0;

        foreach ($venues as $venueId) {
            for ($i = 0; $i <= (int) ($data['repeats'] ?? 0); $i++) {
                $newtime = date(
                    'Y-m-d H:i',
                    strtotime($data['diarydatetime'] . ' + ' . ($i * (int) ($data['interval'] ?? 7)) . ' days')
                );

                Diaryentry::create([
                    'diarisable_id'   => $data['diarisable_id'],
                    'diarisable_type' => $data['diarisable_type'] ?? 'tenant',
                    'venue_id'        => $venueId,
                    'details'         => $data['details'] ?? null,
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
    }

    /**
     * Prefill the create form from whatever the calendar gave us:
     * the typed info object if present, otherwise the raw action arguments.
     */
    protected function prefillBooking(Schema $schema, ?ContextualInfo $info, array $arguments): void
    {
        $start = data_get($info, 'start')
            ?? data_get($info, 'date')
            ?? data_get($info, 'startStr')
            ?? data_get($info, 'dateStr')
            ?? data_get($arguments, 'startStr')
            ?? data_get($arguments, 'dateStr');

        $end = data_get($info, 'end')
            ?? data_get($info, 'endStr')
            ?? data_get($arguments, 'endStr')
            ?? $start;

        $resource = data_get($info, 'resource') ?? data_get($arguments, 'resource');
        $venueId = is_object($resource) && method_exists($resource, 'getId')
            ? $resource->getId()
            : data_get($resource, 'id');

        $venues = $venueId
            ? [$venueId]
            : ($this->record ? [$this->record->id] : []);

        if (! $start) {
            return;
        }

        $schema->fill([
            'diarisable_type' => 'tenant',
            'diarydatetime'   => Carbon::make($start)?->format('Y-m-d H:i'),
            'endtime'         => Carbon::make($end)?->format('H:i'),
            'venue_id'        => $venues,
        ]);
    }

    /**
     * Picked up by Guava Calendar (<camelCaseModel>Schema) for the Diaryentry create/edit forms.
     */
    public function diaryentrySchema(Schema $schema): Schema
    {
        return $schema->components([
            Group::make([
                Select::make('diarisable_id')
                    ->label('Venue user')
                    ->options(function (Get $get) {
                        return match ($get('diarisable_type')) {
                            'group'   => GroupModel::orderBy('groupname')->pluck('groupname', 'id'),
                            'event'   => Event::orderBy('event')->pluck('event', 'id'),
                            'project' => Project::orderBy('project')->pluck('project', 'id'),
                            'course'  => [], // course list was commented out in the original
                            default   => Tenant::orderBy('tenant')->pluck('tenant', 'id'),
                        };
                    })
                    ->searchable()
                    ->required(),
                Select::make('diarisable_type')
                    ->label('User type')
                    ->options([
                        'tenant'  => 'External group',
                        'course'  => setting('general.church_abbreviation') . ' course',
                        'event'   => setting('general.church_abbreviation') . ' event',
                        'group'   => setting('general.church_abbreviation') . ' group',
                        'project' => 'Mission project',
                    ])
                    ->selectablePlaceholder(false)
                    ->live()
                    ->default('tenant'),
            ])->columns(),

            Select::make('venue_id')
                ->label('Venue')
                ->options(Venue::orderBy('venue')->pluck('venue', 'id'))
                ->multiple()
                ->searchable(),

            Textarea::make('details')->rows(5),

            Group::make([
                TextInput::make('repeats')
                    ->label('Number of times to repeat')
                    ->default(0)
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(52),
                Select::make('interval')
                    ->label('How often to repeat')
                    ->options([
                        1  => 'daily',
                        7  => 'weekly',
                        14 => 'fortnightly',
                    ])
                    ->default(7),
                Checkbox::make('calendar')->label('Add to church calendar'),
            ])->columns(),

            Group::make([
                DateTimePicker::make('diarydatetime')
                    ->label('Start time and date')
                    ->native(true)
                    ->default(fn () => date('Y-m-d H:00', strtotime('+2 hours')))
                    ->seconds(false)
                    ->displayFormat('Y-m-d H:i')
                    ->format('Y-m-d H:i')
                    ->required(),
                TimePicker::make('endtime')
                    ->label('End time')
                    ->default(fn () => date('H:00', strtotime('+3 hours')))
                    ->native(true)
                    ->seconds(false)
                    ->required(),
            ])->columns(),
        ]);
    }

    public function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        if ($event instanceof Diaryentry) {
            $startsAt = $info->event->getStart();
            $endsAt = $info->event->getEnd();

            $event->update([
                'diarydatetime' => $startsAt->format('Y-m-d H:i'),
                'endtime' => $endsAt->format('H:i'),
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
            ->queue();

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
                'center' => '',
                'end' => 'today prev,next',
            ],
        ];
    }
}