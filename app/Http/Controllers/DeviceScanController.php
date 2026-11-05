<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\FingerprintTemplate;
use App\Models\Roster;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeviceScanController extends Controller
{
    public function scan(Request $request)
    {
        $data = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'finger_id' => 'required|integer',
        ]);

        // Step 1: Find the fingerprint template.
        $template = FingerprintTemplate::where('device_id', $data['device_id'])
            ->where('fingerprint_id', $data['finger_id'])
            ->first();

        if (! $template) {
            return response()->json([
                'result' => 'unrecognized',
                'message' => 'No staff member is enrolled at this fingerprint slot on this device.',
            ], 404);
        }

        $staffId = $template->staff_id;
        $today = now()->toDateString();
        $now = now();

        // Step 2: Determine check-in or check-out.
        $lastScanToday = AttendanceRecord::where('staff_id', $staffId)
            ->where('scan_date', $today)
            ->orderByDesc('scan_time')
            ->first();

        $scanType = ($lastScanToday && $lastScanToday->scan_type === 'in')
            ? 'out'
            : 'in';

        // Step 3: Calculate attendance status.
        $status = $this->computeStatus(
            $staffId,
            $scanType,
            $today,
            $now->toTimeString()
        );

        // Step 4: Save attendance record.
        $record = AttendanceRecord::create([
            'staff_id'  => $staffId,
            'device_id' => $data['device_id'],
            'scan_type' => $scanType,
            'scan_time' => $now->toTimeString(),
            'scan_date' => $today,
            'status'    => $status,
        ]);

        // Step 5: Record the successful attendance scan in the audit log.
        AuditLog::create([
            'user_id'       => null,
            'action'        => 'ATTENDANCE_RECORDED',
            'table_affected'=> 'attendance_records',
            'record_id'     => $record->id,
            'ip_address'    => $request->ip(),
        ]);

        return response()->json([
            'result'     => 'success',
            'staff_name' => trim(
                $template->staff->first_name . ' ' .
                $template->staff->last_name
            ),
            'scan_type'  => $scanType,
            'status'     => $status,
        ]);
    }

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
            $shiftStart = Carbon::createFromTimeString($roster->shift_start);

            return $scan->gt($shiftStart) ? 'late' : 'on_time';
        }

        $shiftEnd = Carbon::createFromTimeString($roster->shift_end);

        return $scan->lt($shiftEnd) ? 'early_leave' : 'on_time';
    }
}
