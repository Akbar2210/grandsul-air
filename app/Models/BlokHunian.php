<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlokHunian extends Model
{
    protected $guarded = ['id'];

    public function unitHunians()
    {
        return $this->hasMany(UnitHunian::class, 'blok_id');
    }
}