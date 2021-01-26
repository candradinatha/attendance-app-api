<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Models extends Model
{
    //
    protected $fillable = [
        'train', 
        'train_model', 
        'label'
    ];

}
