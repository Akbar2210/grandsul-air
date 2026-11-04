<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifAir extends Model
{
    protected $guarded = ['id'];

    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'tarif_id');
    }
}