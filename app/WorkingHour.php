<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    //
    protected $fillable = [
        'start_time', 
        'end_time' 
    ];
}
