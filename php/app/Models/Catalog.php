<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    protected $fillable = [
        'name',
    ];

    public function articles()
    {
        return $this->hasMany('App\Article');
    }
}
