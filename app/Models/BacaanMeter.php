<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BacaanMeter extends Model
{
    protected $guarded = ['id'];

    public function unit()
    {
        return $this->belongsTo(UnitHunian::class, 'unit_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tagihan()
    {
        return $this->hasOne(Tagihan::class, 'bacaan_meter_id');
    }
}