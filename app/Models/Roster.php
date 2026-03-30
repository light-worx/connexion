<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roster extends Model
{
    use SoftDeletes;

    public $table = 'rosters';
    protected $guarded = ['id'];

    public function rostergroups(): HasMany
    {
        return $this->hasMany(Rostergroup::class);
    }
}
