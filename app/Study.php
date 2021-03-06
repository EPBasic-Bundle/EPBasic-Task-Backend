<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Study extends Model
{
    protected $table = 'studies';

    public function years()
    {
        return $this->hasMany('App\Year', 'study_id');
    }
}
