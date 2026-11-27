<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Models\Staff;

class AttendanceWebController extends Controller
{
    

public function index(Request $request)
{
    $user = $request->user();
    $role = $user->role->name ?? null;

    // Management roles can view all staff attendance.
    $canViewAll = in_array($role, [
        'System Administrator',
        'HR Administrator',
        'Supervisor / Manager'
    ]);

    $isSystemAdmin = $role === 'System Administrator';

    $query = AttendanceRecord::with([
        'staff:id,first_name,last_name,staff_no',
        'device:id,device_name'
    ]);

    $noStaffLink = false;

    if ($canViewAll) {

        if ($request->filled('date')) {
            $query->where('scan_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

    } elseif ($user->staff_id) {

        // Healthcare workers only see their own attendance.
        $query->where('staff_id', $user->staff_id);

    } else {

        $query->whereRaw('1 = 0');
        $noStaffLink = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Live attendance JSON response
    |--------------------------------------------------------------------------
    | JavaScript requests this response every 3 seconds.
    | It returns the latest records without reloading the page.
    */

    if (
        $request->boolean('live') &&
        $request->expectsJson()
    ) {
        $liveRecords = (clone $query)
            ->orderByDesc('scan_date')
            ->orderByDesc('scan_time')
            ->limit(30)
            ->get()
            ->map(function ($record) use ($isSystemAdmin) {

                return [
                    'id' => $record->id,

                    'staff' => $record->staff ? [
                        'id' => $record->staff->id,
                        'first_name' => $record->staff->first_name,
                        'last_name' => $record->staff->last_name,
                        'staff_no' => $record->staff->staff_no,
                    ] : null,

                    'device' => $record->device ? [
                        'device_name' => $record->device->device_name,
                    ] : null,

                    'scan_date' => $record->scan_date,

                    'scan_date_formatted' =>
                        \Carbon\Carbon::parse($record->scan_date)
                            ->format('d M Y'),

                    'scan_time' => $record->scan_time,

                    'scan_time_formatted' =>
                        \Carbon\Carbon::parse($record->scan_time)
                            ->format('H:i:s'),

                    'scan_type' => $record->scan_type,

                    'status' => $record->status,

                    'delete_url' => $isSystemAdmin
                        ? route('attendance.destroy', $record)
                        : null,

                    'csrf_token' => $isSystemAdmin
                        ? csrf_token()
                        : null,
                ];
            });

        return response()->json([
            'records' => $liveRecords,
        ]);
    }

    // Normal page loading and pagination remain unchanged.
    $records = $query
        ->orderByDesc('scan_date')
        ->orderByDesc('scan_time')
        ->paginate(20)
        ->withQueryString();

    return view('attendance.index', compact(
        'records',
        'canViewAll',
        'isSystemAdmin',
        'noStaffLink'
    ));
}



    /**
     * NFR-08: attendance records are immutable once committed. This mirrors
     * the API's own AttendanceRecordController::destroy() — restricted to
     * System Administrator, and logged before deletion. The route itself
     * also carries role:System Administrator middleware as a second layer.
     */
    public function destroy(Request $request, AttendanceRecord $attendanceRecord)
    {
        AuditLog::create([
            'user_id'        => $request->user()->id,
            'action'         => 'ATTENDANCE_RECORD_DELETED',
            'table_affected' => 'attendance_records',
            'record_id'      => $attendanceRecord->id,
            'ip_address'     => $request->ip(),
        ]);

        $attendanceRecord->delete();

        return redirect()->route('attendance.index')->with('status', 'Attendance record deleted and logged to the audit trail.');
    }


/**
 * Show the full attendance history for one staff member.
 */
public function staffHistory(Request $request, Staff $staff)
{
    $filters = $request->validate([
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ]);

    $query = AttendanceRecord::with('device')
        ->where('staff_id', $staff->id);

    // Apply optional date filters.
    if (!empty($filters['start_date'])) {
        $query->whereDate('scan_date', '>=', $filters['start_date']);
    }

    if (!empty($filters['end_date'])) {
        $query->whereDate('scan_date', '<=', $filters['end_date']);
    }

    // Calculate statistics for the selected period.
    $totalScans = (clone $query)->count();

    $daysWithScans = (clone $query)
        ->distinct()
        ->count('scan_date');

    $lateScans = (clone $query)
    ->where('status', 'late')
    ->count();

    // Retrieve the attendance history.
    $records = (clone $query)
        ->orderByDesc('scan_date')
        ->orderByDesc('scan_time')
        ->paginate(25)
        ->withQueryString();

    return view('attendance.staff-history', [
        'staff' => $staff,
        'records' => $records,
        'totalScans' => $totalScans,
        'daysWithScans' => $daysWithScans,
        'lateScans' => $lateScans,
        'startDate' => $filters['start_date'] ?? '',
        'endDate' => $filters['end_date'] ?? '',
    ]);
}

}
