<?php

namespace App\Models;

use App\Models\Diaryentry;
use App\Traits\Taggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use Taggable;

    public $table = 'projects';
    protected $guarded = ['id'];

    public function diaryentries(): MorphMany
    {
        return $this->morphMany(Diaryentry::class,'diarisable');
    }
}
