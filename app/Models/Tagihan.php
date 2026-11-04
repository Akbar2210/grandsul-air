<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $guarded = ['id'];

    public function unit()
    {
        return $this->belongsTo(UnitHunian::class, 'unit_id');
    }

    public function bacaanMeter()
    {
        return $this->belongsTo(BacaanMeter::class, 'bacaan_meter_id');
    }

    public function tarif()
    {
        return $this->belongsTo(TarifAir::class, 'tarif_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'tagihan_id');
    }

    public function digitalSignature()
    {
        return $this->hasOne(DigitalSignature::class, 'tagihan_id');
    }
}