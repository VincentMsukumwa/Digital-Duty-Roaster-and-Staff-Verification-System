<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class LeaveRequestWebController extends Controller
{
    /**
     * Management roles see everyone's leave requests; anyone else only
     * sees their own (via their linked staff_id) — same pattern as the
     * Rosters "my schedule" filtering.
     */
    protected function canManage(Request $request): bool
    {
        $role = $request->user()->role->name ?? null;

        return in_array($role, ['System Administrator', 'Supervisor / Manager']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $canManage = $this->canManage($request);

        $query = LeaveRequest::with(['staff', 'approver']);

        if ($canManage) {
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } elseif ($user->staff_id) {
            $query->where('staff_id', $user->staff_id);
        } else {
            // Not linked to a staff profile — nothing to show.
            $query->whereRaw('1 = 0');
        }

        $leaveRequests = $query->orderByDesc('start_date')->paginate(15)->withQueryString();

        return view('leave-requests.index', compact('leaveRequests', 'canManage'));
    }

    public function create(Request $request)
    {
        if (! $request->user()->staff_id) {
            return redirect()->route('leave-requests.index')
                ->with('status', "Your account isn't linked to a staff profile, so you can't submit a leave request. Ask your System Administrator to link it.");
        }

        return view('leave-requests.create');
    }

    public function store(Request $request)
    {
        if (! $request->user()->staff_id) {
            return redirect()->route('leave-requests.index')
                ->with('status', "Your account isn't linked to a staff profile, so you can't submit a leave request.");
        }

        $data = $request->validate([
            'leave_type' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string',
        ]);

        $data['staff_id'] = $request->user()->staff_id;
        $data['status'] = 'pending';

        LeaveRequest::create($data);

        return redirect()->route('leave-requests.index')->with('status', 'Leave request submitted.');
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->route('leave-requests.index')->with('status', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status'      => 'rejected',
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->route('leave-requests.index')->with('status', 'Leave request rejected.');
    }

    /**
     * A staff member can cancel their own request while it's still pending.
     * Management can delete any request regardless of status.
     */
    public function destroy(Request $request, LeaveRequest $leaveRequest)
    {
        $user = $request->user();
        $isOwner = $user->staff_id && $user->staff_id === $leaveRequest->staff_id;

        if (! $this->canManage($request) && ! ($isOwner && $leaveRequest->status === 'pending')) {
            return redirect()->route('leave-requests.index')
                ->with('status', 'You can only cancel your own pending requests.');
        }

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')->with('status', 'Leave request removed.');
    }
}
