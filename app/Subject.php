<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';

    public function books(){
        return $this->hasMany('App\Book', 'subject_id');
    }
}
