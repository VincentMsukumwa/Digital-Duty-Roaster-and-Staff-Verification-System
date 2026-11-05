<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    use HasFactory;

    protected $table = 'rosters';

    protected $fillable = [
        'staff_id',
        'department_id',
        'created_by',
        'shift_start',
        'shift_end',
        'work_days',
        'effective_date',
        'end_date',
        'status',
        'published_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date'       => 'date',
        'published_at'   => 'datetime',
    ];

    /**
     * The staff member this roster assignment is for.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * The department this roster assignment covers.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * The supervisor who created this roster (FR-05).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Work days as an array, e.g. ['Mon', 'Tue', 'Wed'].
     * Stored as a comma-separated string (known prototype limitation).
     */
    public function getWorkDaysArrayAttribute(): array
    {
        return $this->work_days ? explode(',', $this->work_days) : [];
    }
}
