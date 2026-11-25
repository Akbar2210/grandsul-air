<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tgl_kirim' => 'datetime',
        'status_terkirim' => 'boolean',
        'is_read' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(UnitHunian::class, 'unit_id');
    }
}
