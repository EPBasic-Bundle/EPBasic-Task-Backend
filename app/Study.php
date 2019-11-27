<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Study extends Model
{
    protected $table = 'studies';

    public function evaluations()
    {
        return $this->hasMany('App\Evaluation', 'study_id');
    }
}
