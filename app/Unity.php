<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unity extends Model
{
    protected $table = 'units';

    public function tasks(){
        return $this->hasMany('App\Task', 'unity_id');
    }
}
