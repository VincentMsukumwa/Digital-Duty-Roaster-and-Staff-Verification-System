<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['staff:id,first_name,last_name,staff_no', 'approver:id,username']);

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderByDesc('start_date')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_id'   => 'required|exists:staff,id',
            'leave_type' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string',
        ]);

        $data['status'] = 'pending';

        return response()->json(LeaveRequest::create($data), 201);
    }

    public function show(LeaveRequest $leaveRequest)
    {
        return $leaveRequest->load(['staff', 'approver']);
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $data = $request->validate([
            'leave_type' => 'sometimes|required|string|max:50',
            'start_date' => 'sometimes|required|date',
            'end_date'   => 'sometimes|required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string',
        ]);

        $leaveRequest->update($data);

        return $leaveRequest;
    }

    /**
     * Supervisor/HR admin approves the request (FR referenced in leave_requests
     * schema comment: cross-referenced with attendance for ghost worker checks).
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status'      => 'approved',
            'approved_by' => $request->user()?->id,
        ]);

        return $leaveRequest;
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status'      => 'rejected',
            'approved_by' => $request->user()?->id,
        ]);

        return $leaveRequest;
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();

        return response()->json(null, 204);
    }
}
