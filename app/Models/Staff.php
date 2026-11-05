<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'department_id',
        'staff_no',
        'first_name',
        'last_name',
        'gender',
        'phone',
        'email',
        'position',
        'date_hired',
        'status',
    ];

    protected $casts = [
        'date_hired' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function fingerprintTemplates()
    {
        return $this->hasMany(FingerprintTemplate::class);
    }

    public function rosters()
    {
        return $this->hasMany(Roster::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
