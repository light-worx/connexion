<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    protected $dates = ['deleted_at'];
    public $table = 'households';
    protected $guarded = ['id'];

    public function individuals(): HasMany
    {
        return $this->hasMany(Individual::class);
    }

    public function getNameAttribute()
    {
        return $this->addressee;
    }
}
