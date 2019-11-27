<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    protected $table = 'years';

    public function evaluations()
    {
        return $this->hasMany('App\Evaluation', 'year_id');
    }
}
