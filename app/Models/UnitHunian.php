<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitHunian extends Model
{
    protected $guarded = ['id'];

    public function blok()
    {
        return $this->belongsTo(BlokHunian::class, 'blok_id');
    }

    public function bacaanMeters()
    {
        return $this->hasMany(BacaanMeter::class, 'unit_id');
    }

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'unit_id');
    }

    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class, 'unit_id');
    }
}
