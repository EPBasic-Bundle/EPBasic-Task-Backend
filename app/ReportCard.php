<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    protected $table = 'report_cards';

    public function marks()
    {
        return $this->hasMany('App\Mark', 'report_card_id')->orderBy('subject_id', 'asc');
    }
}
