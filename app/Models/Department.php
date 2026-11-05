<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Staff members assigned to this department.
     */
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Roster assignments scheduled for this department.
     */
    public function rosters()
    {
        return $this->hasMany(Roster::class);
    }
}
