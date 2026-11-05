<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'devices';

    protected $fillable = [
        'device_name',
        'device_uid',
        'location',
        'ip_address',
        'status',
        'last_sync',
    ];

    protected $casts = [
        'last_sync' => 'datetime',
    ];

    /**
     * Fingerprint templates enrolled via this device.
     */
    public function fingerprintTemplates()
    {
        return $this->hasMany(FingerprintTemplate::class);
    }

    /**
     * Attendance scans recorded at this device.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function commands()
{
    return $this->hasMany(DeviceCommand::class);
}


}
