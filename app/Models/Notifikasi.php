<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $guarded = ['id'];

    public function unit()
    {
        return $this->belongsTo(UnitHunian::class, 'unit_id');
    }
}