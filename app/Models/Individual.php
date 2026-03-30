<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Individual extends Model
{
    protected $dates = ['deleted_at'];
    public $table = 'individuals';
    protected $guarded = ['id'];
    protected $casts = ['app'=>'array'];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->surname;
    }

    public function getNameAttribute()
    {
        return $this->firstname . ' ' . $this->surname;
    }

    public function getLastseenAttribute() {
        $attend=Attendance::where('individual_id',$this->id)->orderBy('attendancedate','DESC')->first();
        if ($attend){
            return date('d M Y',strtotime($attend->attendancedate)) . " (" . $attend->service . ")";
        }
    }

}
