<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Roster;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportWebController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role->name ?? null;

        // Only management roles can access organization-wide reports.
        $canViewAll = in_array($role, [
            'System Administrator',
            'HR Administrator',
            'Supervisor / Manager',
        ]);

        // Validate report filters.
        $filters = $request->validate([
            'date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'staff_id' => ['nullable', 'integer', 'exists:staff,id'],
            'report_type' => [
                'nullable',
                'in:daily,department,staff,roster,exceptions',
            ],
        ]);

        $reportType = $filters['report_type'] ?? 'daily';
        $date = $filters['date'] ?? today()->toDateString();
        $startDate = $filters['start_date'] ?? $date;
        $endDate = $filters['end_date'] ?? $date;

        /*
         * ATTENDANCE REPORT
         */
        $attendanceQuery = AttendanceRecord::with([
            'staff.department',
            'device',
        ]);

        if (!$canViewAll) {
            // Healthcare workers can only see their own attendance.
            if ($user->staff_id) {
                $attendanceQuery->where('staff_id', $user->staff_id);
            } else {
                $attendanceQuery->whereRaw('1 = 0');
            }
        }

        if ($reportType === 'daily') {
            $attendanceQuery->whereDate('scan_date', $date);
        } else {
            $attendanceQuery
                ->whereDate('scan_date', '>=', $startDate)
                ->whereDate('scan_date', '<=', $endDate);
        }

        if ($canViewAll && !empty($filters['staff_id'])) {
            $attendanceQuery->where(
                'staff_id',
                $filters['staff_id']
            );
        }

        if ($canViewAll && !empty($filters['department_id'])) {
            $departmentId = $filters['department_id'];

            $attendanceQuery->whereHas('staff', function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            });
        }

        if ($reportType === 'exceptions') {
            $attendanceQuery->whereIn('status', [
                'late',
                'early_leave',
                'absent',
            ]);
        }

        $attendanceRecords = $attendanceQuery
            ->orderByDesc('scan_date')
            ->orderByDesc('scan_time')
            ->get();

        /*
         * DUTY-ROSTER REPORT
         */
        $rosterQuery = Roster::with([
            'staff.department',
            'department',
        ]);

        if (!$canViewAll) {
            if ($user->staff_id) {
                $rosterQuery->where('staff_id', $user->staff_id);
            } else {
                $rosterQuery->whereRaw('1 = 0');
            }
        }

        $rosterQuery
            ->whereDate('effective_date', '<=', $endDate)
            ->where(function ($query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate);
            });

        if ($canViewAll && !empty($filters['staff_id'])) {
            $rosterQuery->where('staff_id', $filters['staff_id']);
        }

        if ($canViewAll && !empty($filters['department_id'])) {
            $rosterQuery->where(
                'department_id',
                $filters['department_id']
            );
        }

        $rosters = $rosterQuery
            ->orderBy('effective_date')
            ->get();

        /*
         * SUMMARY COUNTS
         */
        $totalRecords = $attendanceRecords->count();

        $totalStaffPresent = $attendanceRecords
            ->pluck('staff_id')
            ->unique()
            ->count();

        $lateArrivals = $attendanceRecords
            ->where('status', 'late')
            ->count();

        $earlyDepartures = $attendanceRecords
            ->where('status', 'early_leave')
            ->count();

        $departments = Department::orderBy('name')->get();

        $staffMembers = $canViewAll
            ? Staff::orderBy('first_name')->orderBy('last_name')->get()
            : collect();

        return view('reports.index', compact(
            'reportType',
            'date',
            'startDate',
            'endDate',
            'filters',
            'attendanceRecords',
            'rosters',
            'departments',
            'staffMembers',
            'canViewAll',
            'totalRecords',
            'totalStaffPresent',
            'lateArrivals',
            'earlyDepartures'
        ));
    }
}
