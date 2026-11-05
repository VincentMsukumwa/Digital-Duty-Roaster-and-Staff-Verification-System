<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Roster;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRecord::with([
            'staff:id,first_name,last_name,staff_no',
            'device:id,device_name'
        ]);

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('date')) {
            $query->where('scan_date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderByDesc('scan_date')
            ->orderByDesc('scan_time')
            ->paginate(30);
    }

    /**
     * Create an attendance record and log the action.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_id'  => 'required|exists:staff,id',
            'device_id' => 'nullable|exists:devices,id',
            'scan_type' => 'required|in:in,out',
            'scan_time' => 'nullable|date_format:H:i:s',
            'scan_date' => 'nullable|date',
        ]);

        $scanDate = $data['scan_date'] ?? now()->toDateString();
        $scanTime = $data['scan_time'] ?? now()->toTimeString();

        $data['scan_date'] = $scanDate;
        $data['scan_time'] = $scanTime;

        $data['status'] = $this->computeStatus(
            $data['staff_id'],
            $data['scan_type'],
            $scanDate,
            $scanTime
        );

        // Save attendance record.
        $record = AttendanceRecord::create($data);

        // Log attendance creation.
        AuditLog::create([
            'user_id'        => $request->user()?->id,
            'action'         => 'ATTENDANCE_RECORD_CREATED',
            'table_affected' => 'attendance_records',
            'record_id'      => $record->id,
            'ip_address'     => $request->ip(),
        ]);

        return response()->json($record, 201);
    }

    public function show(AttendanceRecord $attendanceRecord)
    {
        return $attendanceRecord->load(['staff', 'device']);
    }

    /**
     * Only a System Administrator may delete attendance records.
     * The deletion is logged before the record is removed.
     */
    public function destroy(Request $request, AttendanceRecord $attendanceRecord)
    {
        if (! $request->user()?->hasRole('System Administrator')) {
            return response()->json([
                'message' => 'Only a System Administrator may delete an attendance record.',
            ], 403);
        }

        AuditLog::create([
            'user_id'        => $request->user()?->id,
            'action'         => 'ATTENDANCE_RECORD_DELETED',
            'table_affected' => 'attendance_records',
            'record_id'      => $attendanceRecord->id,
            'ip_address'     => $request->ip(),
        ]);

        $attendanceRecord->delete();

        return response()->json(null, 204);
    }

    /**
     * Calculate attendance status using the published roster
     * and configured late threshold.
     */
    protected function computeStatus(
        int $staffId,
        string $scanType,
        string $scanDate,
        string $scanTime
    ): string {
        $roster = Roster::where('staff_id', $staffId)
            ->where('status', 'published')
            ->where('effective_date', '<=', $scanDate)
            ->where(function ($q) use ($scanDate) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $scanDate);
            })
            ->first();

        if (! $roster) {
            return 'on_time';
        }

        $scan = Carbon::createFromTimeString($scanTime);

        if ($scanType === 'in') {
            $lateThreshold = (int) Setting::get('late_threshold_minutes', 15);

            $shiftStart = Carbon::createFromTimeString(
                $roster->shift_start
            )->addMinutes($lateThreshold);

            return $scan->gt($shiftStart) ? 'late' : 'on_time';
        }

        $shiftEnd = Carbon::createFromTimeString($roster->shift_end);

        return $scan->lt($shiftEnd) ? 'early_leave' : 'on_time';
    }
}
