<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Staff;
use Illuminate\Http\Request;
use App\Models\DeviceCommand;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use App\Models\FingerprintTemplate;
use Illuminate\Support\Facades\DB;

class StaffWebController extends Controller
{
    /**
     * List page — searchable, filterable, paginated.
     */
    

public function index(Request $request)
{
    $query = Staff::with([
        'department',
        'fingerprintTemplates'
    ])->orderBy('last_name');

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'ilike', "%{$search}%")
              ->orWhere('last_name', 'ilike', "%{$search}%")
              ->orWhere('staff_no', 'ilike', "%{$search}%");
        });
    }

    if ($request->filled('department_id')) {
        $query->where('department_id', $request->department_id);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $staff = $query->paginate(15)->withQueryString();

    $pendingEnrollmentStaffIds = DeviceCommand::where('device_id', 1)
        ->where('command', 'enroll')
        ->where('status', 'pending')
        ->pluck('staff_id')
        ->toArray();

    $departments = Department::orderBy('name')->get();

    return view('staff.index', compact(
        'staff',
        'departments',
        'pendingEnrollmentStaffIds'
    ));
}



    /**
     * Show the "add new staff" form.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('staff.create', compact('departments'));
    }

public function store(Request $request)
{
    $data = $request->validate([
        'department_id' => 'nullable|exists:departments,id',
        'staff_no'      => 'required|string|max:30|unique:staff,staff_no',
        'first_name'    => 'required|string|max:100',
        'last_name'     => 'required|string|max:100',
        'gender'        => 'nullable|string|max:10',
        'phone'         => 'nullable|string|max:20',
        'email'         => 'nullable|email|max:150|unique:staff,email',
        'position'      => 'nullable|string|max:100',
        'date_hired'    => 'nullable|date',
        'status'        => 'nullable|in:active,inactive,suspended',
    ]);

    // Create the staff member.
    $staff = Staff::create($data);

    // Record the action in the audit log.
    AuditLog::create([
        'user_id'        => $request->user()?->id,
        'action'         => 'STAFF_CREATED',
        'table_affected' => 'staff',
        'record_id'      => $staff->id,
        'ip_address'     => $request->ip(),
    ]);

    return redirect()
        ->route('staff.index')
        ->with('status', 'Staff member added successfully.');
}


    /**
     * Show the "edit staff" form, pre-filled.
     */
    public function edit(Staff $staff)
    {
        $departments = Department::orderBy('name')->get();

        return view('staff.edit', compact('staff', 'departments'));
    }

   
public function update(Request $request, Staff $staff)
{
    $data = $request->validate([
        'department_id' => 'nullable|exists:departments,id',
        'staff_no'      => 'required|string|max:30|unique:staff,staff_no,' . $staff->id,
        'first_name'    => 'required|string|max:100',
        'last_name'     => 'required|string|max:100',
        'gender'        => 'nullable|string|max:10',
        'phone'         => 'nullable|string|max:20',
        'email'         => 'nullable|email|max:150|unique:staff,email,' . $staff->id,
        'position'      => 'nullable|string|max:100',
        'date_hired'    => 'nullable|date',
        'status'        => 'nullable|in:active,inactive,suspended',
    ]);

    $staff->update($data);

    AuditLog::create([
        'user_id'        => $request->user()?->id,
        'action'         => 'STAFF_UPDATED',
        'table_affected' => 'staff',
        'record_id'      => $staff->id,
        'ip_address'     => $request->ip(),
    ]);

    return redirect()
        ->route('staff.index')
        ->with('status', 'Staff member updated successfully.');
}


   

public function destroy(Request $request, Staff $staff)
{
    $staffId = $staff->id;

    // Archive the staff member instead of permanently deleting them.
    $staff->delete();

    // Record the archive action.
    AuditLog::create([
        'user_id'        => $request->user()?->id,
        'action'         => 'STAFF_ARCHIVED',
        'table_affected' => 'staff',
        'record_id'      => $staffId,
        'ip_address'     => $request->ip(),
    ]);

    return redirect()
        ->route('staff.index')
        ->with('status', 'Staff member archived successfully.');
}




public function statistics(Request $request, Staff $staff)
{
    $data = $request->validate([
        'period' => 'required|in:week,month,custom',
        'start_date' => 'required_if:period,custom|nullable|date',
        'end_date' => 'required_if:period,custom|nullable|date|after_or_equal:start_date',
    ]);

    // Determine the date range.
    if ($data['period'] === 'week') {
        $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::now()->endOfWeek(Carbon::SUNDAY);
    } elseif ($data['period'] === 'month') {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
    } else {
        $startDate = Carbon::parse($data['start_date'])->startOfDay();
        $endDate = Carbon::parse($data['end_date'])->endOfDay();
    }

    $records = AttendanceRecord::where('staff_id', $staff->id)
        ->whereBetween('scan_date', [
            $startDate->toDateString(),
            $endDate->toDateString(),
        ])
        ->orderByDesc('scan_date')
        ->orderByDesc('scan_time')
        ->get();

    return response()->json([
        'staff' => [
            'id' => $staff->id,
            'name' => trim($staff->first_name . ' ' . $staff->last_name),
            'staff_no' => $staff->staff_no,
        ],

        'period' => [
            'type' => $data['period'],
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ],

        'statistics' => [
            'total_scans' => $records->count(),

            'late_arrivals' => $records
                ->where('status', 'late')
                ->count(),

            'early_departures' => $records
                ->where('status', 'early_leave')
                ->count(),

            'attendance_days' => $records
                ->pluck('scan_date')
                ->map(fn ($date) => Carbon::parse($date)->toDateString())
                ->unique()
                ->count(),
        ],

        'records' => $records->map(function ($record) {
            return [
                'date' => Carbon::parse($record->scan_date)->toDateString(),
                'time' => $record->scan_time,
                'scan_type' => $record->scan_type,
                'status' => $record->status,
            ];
        })->values(),
    ]);
}




   

public function startFingerprintRegistration(Request $request, Staff $staff)
{
    $deviceId = 1;

    return DB::transaction(function () use ($request, $staff, $deviceId) {

        // Lock the staff record to prevent simultaneous enrollment requests.
        $lockedStaff = Staff::whereKey($staff->id)
            ->lockForUpdate()
            ->firstOrFail();

        // Rule 1: A staff member can only have one fingerprint.
        $existingFingerprint = FingerprintTemplate::where(
            'staff_id',
            $lockedStaff->id
        )->exists();

        if ($existingFingerprint) {
            return redirect()
                ->route('staff.index')
                ->with(
                    'error',
                    'This staff member already has a registered fingerprint. Fingerprint replacement is not permitted.'
                );
        }

        // Rule 2: Do not allow another pending enrollment for this staff member.
        $staffHasPendingEnrollment = DeviceCommand::where('staff_id', $lockedStaff->id)
            ->where('device_id', $deviceId)
            ->where('command', 'enroll')
            ->where('status', 'pending')
            ->exists();

        if ($staffHasPendingEnrollment) {
            return redirect()
                ->route('staff.index')
                ->with(
                    'error',
                    'This staff member already has a pending fingerprint enrollment.'
                );
        }

        // Rule 3: Only one command can be pending on the device at a time.
        $deviceHasPendingCommand = DeviceCommand::where('device_id', $deviceId)
            ->where('status', 'pending')
            ->exists();

        if ($deviceHasPendingCommand) {
            return redirect()
                ->route('staff.index')
                ->with(
                    'error',
                    'The attendance device already has a pending command. Please wait for it to finish.'
                );
        }

        // Create the first enrollment command.
        DeviceCommand::create([
            'device_id' => $deviceId,
            'staff_id'  => $lockedStaff->id,
            'command'   => 'enroll',
            'status'    => 'pending',
        ]);

        // Record the action.
        AuditLog::create([
            'user_id'        => $request->user()?->id,
            'action'         => 'FINGERPRINT_ENROLLMENT_STARTED',
            'table_affected' => 'staff',
            'record_id'      => $lockedStaff->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('staff.index')
            ->with(
                'status',
                'Fingerprint registration started for ' .
                $lockedStaff->full_name .
                '. Place the staff member\'s finger on the sensor when the device enters enrollment mode.'
            );
    });
}








/**
 * List archived staff members.
 */
public function archived()
{
    $staff = Staff::onlyTrashed()
        ->with('department')
        ->orderBy('last_name')
        ->paginate(15);

    return view('staff.archived', compact('staff'));
}

/**
 * Restore an archived staff member.
 */
public function restore(Request $request, $id)
{
    $staff = Staff::onlyTrashed()->findOrFail($id);

    $staff->restore();

    AuditLog::create([
        'user_id'        => $request->user()?->id,
        'action'         => 'STAFF_RESTORED',
        'table_affected' => 'staff',
        'record_id'      => $staff->id,
        'ip_address'     => $request->ip(),
    ]);

    return redirect()
        ->route('staff.archived')
        ->with('status', 'Staff member restored successfully.');
}


}
