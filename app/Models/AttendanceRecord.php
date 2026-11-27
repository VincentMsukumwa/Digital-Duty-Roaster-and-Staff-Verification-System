<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';

    protected $fillable = [
        'staff_id',
        'device_id',
        'scan_type',
        'scan_time',
        'scan_date',
        'status',
    ];

    protected $casts = [
        'scan_date' => 'date',
    ];

    /**
     * The staff member who was scanned.
     */
    public function staff()
{
    return $this->belongsTo(Staff::class, 'staff_id')->withTrashed();
}

    /**
     * The device the scan was recorded on.
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
