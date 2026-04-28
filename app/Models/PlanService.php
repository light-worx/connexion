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

    public function setitems()
    {
        return $this->hasMany(Setitem::class);
    }
}
