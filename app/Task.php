<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    public function pages(){
        return $this->hasMany('App\Page', 'task_id')->orderBy('number');
    }
}
