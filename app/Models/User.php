<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'users';

    protected $fillable = [
        'staff_id',
        'role_id',
        'username',
        'email',
        'password',
        'last_login',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'password'   => 'hashed',
    ];

    /**
     * The staff/HR profile linked to this login account.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * The role assigned to this user (drives access control).
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Notifications delivered to this user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Audit log entries recorded against actions this user performed.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Rosters this user (a supervisor) created.
     */
    public function createdRosters()
    {
        return $this->hasMany(Roster::class, 'created_by');
    }

    /**
     * Leave requests this user (a supervisor) approved or rejected.
     */
    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    /**
     * Reports this user generated.
     */
    public function generatedReports()
    {
        return $this->hasMany(ReportsLog::class, 'generated_by');
    }

    /**
     * Convenience helper for role-based access checks, e.g. $user->hasRole('HR Administrator')
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }
}
