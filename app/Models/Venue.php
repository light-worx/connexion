<?php

namespace App\Models;

use App\Traits\Taggable;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model implements Resourceable
{
    use Taggable;

    public $table = 'venues';
    protected $guarded = ['id'];

    public function toCalendarResource(): CalendarResource {
        return CalendarResource::make($this)
            ->title($this->venue);
    }
}
