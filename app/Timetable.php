<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model {

    protected $table = 'timetables';

    protected $fillable = [
        'rows', 'updated_at'
    ];

    public function subjects(){
        return $this->hasMany('App\TimetableSubject', 'timetable_id');
    }

    public function hours(){
        return $this->hasMany('App\TimetableHour', 'timetable_id');
    }
}
