<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $table = 'leave_requests';

    protected $fillable = [
        'staff_id',
        'approved_by',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /**
     * The staff member who submitted this leave request.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * The supervisor/HR admin who approved or rejected this request.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
