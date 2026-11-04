<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DigitalSignature extends Model
{
    protected $guarded = ['id'];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }
}