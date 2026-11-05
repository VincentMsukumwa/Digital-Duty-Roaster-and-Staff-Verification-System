<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerprintTemplate extends Model
{
    use HasFactory;

    protected $table = 'fingerprint_templates';

    protected $fillable = [
        'staff_id',
        'device_id',
        'fingerprint_id',
        'template_data',
        'finger_position',
        'enrolled_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
    ];

    /**
     * The staff member this fingerprint template belongs to.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * The device used to enrol this template.
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
