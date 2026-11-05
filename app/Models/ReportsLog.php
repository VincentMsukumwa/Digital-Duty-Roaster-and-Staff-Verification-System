<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportsLog extends Model
{
    use HasFactory;

    protected $table = 'reports_log';

    protected $fillable = [
        'generated_by',
        'report_type',
        'date_range_start',
        'date_range_end',
        'file_path',
    ];

    protected $casts = [
        'date_range_start' => 'date',
        'date_range_end'   => 'date',
    ];

    /**
     * The user (supervisor or HR admin) who generated this report.
     */
    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
