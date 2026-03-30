<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Post;
use App\Models\Service;

class Person extends Model
{
    public $table = 'persons';
    protected $guarded = ['id'];
    public $timestamps = false;

    protected $casts = [
        'role' => 'array',
    ];

    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->surname;
    }

    public function posts(): HasMany
    {
        if ($this->moduleOn('website')) {
            return $this->hasMany(Post::class);
        } else {
            return $this->whereNull('id');
        }
        
    }

    public function services(): HasMany
    {
        if ($this->moduleOn('worship')) {
            return $this->hasMany(Service::class);
        } else {
            return $this->whereNull('id');
        }
    }
}
