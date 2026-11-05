<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with('department')->orderBy('last_name');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('staff_no', 'ilike', "%{$search}%");
            });
        }

        return $query->paginate(20);
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

        return response()->json(Staff::create($data), 201);
    }

    public function show(Staff $staff)
    {
        return $staff->load(['department', 'user', 'rosters', 'leaveRequests']);
    }

    public function update(Request $request, Staff $staff)
    {
        $data = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'staff_no'      => 'sometimes|required|string|max:30|unique:staff,staff_no,' . $staff->id,
            'first_name'    => 'sometimes|required|string|max:100',
            'last_name'     => 'sometimes|required|string|max:100',
            'gender'        => 'nullable|string|max:10',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:150|unique:staff,email,' . $staff->id,
            'position'      => 'nullable|string|max:100',
            'date_hired'    => 'nullable|date',
            'status'        => 'nullable|in:active,inactive,suspended',
        ]);

        $staff->update($data);

        return $staff;
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return response()->json(null, 204);
    }

    /**
     * Attendance history for a single staff member over an optional date range —
     * used for the personal schedule/history screen (Healthcare Worker role).
     */
    public function attendanceHistory(Request $request, Staff $staff)
    {
        $query = $staff->attendanceRecords()->orderByDesc('scan_date')->orderByDesc('scan_time');

        if ($request->filled('from')) {
            $query->where('scan_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('scan_date', '<=', $request->to);
        }

        return $query->paginate(30);
    }
}
