<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BacaanMeter extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'gps_unverified' => 'boolean',
        'is_ocr_corrected' => 'boolean',
        'confidence_score' => 'float',
    ];

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