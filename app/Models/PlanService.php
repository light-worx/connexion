<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanService extends Model
{
    protected $guarded = ['id'];

    public function plan()
    {
        return $this->belongsTo(ServicePlan::class, 'service_plan_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function setitems()
    {
        return $this->hasMany(Setitem::class);
    }

    public function songSetitems()
    {
        return $this->setitems()->where('content_type', 'song');
    }

    public function prayerSetitems()
    {
        return $this->setitems()->where('content_type', 'prayer');
    }
}
