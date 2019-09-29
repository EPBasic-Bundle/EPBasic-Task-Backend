<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'tasks';

    public function pages(){
        return $this->hasMany('App\Page', 'task_id')->orderBy('number');
    }

    public function book(){
        return $this->hasOne('App\Book', 'id', 'book_id');
    }
}
