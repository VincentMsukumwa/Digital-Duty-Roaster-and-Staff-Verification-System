<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'staff_id',
        'command',
        'status',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}