<?php

namespace App\Http\Controllers;

use App\Models\Roster;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function index(Request $request)
    {
        $query = Roster::with(['staff:id,first_name,last_name,staff_no', 'department', 'creator']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderByDesc('effective_date')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_id'       => 'required|exists:staff,id',
            'department_id'  => 'nullable|exists:departments,id',
            'shift_start'    => 'required|date_format:H:i',
            'shift_end'      => 'required|date_format:H:i',
            'work_days'      => 'nullable|string|max:50',
            'effective_date' => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:effective_date',
            'status'         => 'nullable|in:draft,published,archived',
        ]);

        // Roster creator (FR-05) is always the authenticated supervisor.
        $data['created_by'] = $request->user()?->id;

        return response()->json(Roster::create($data), 201);
    }

    public function show(Roster $roster)
    {
        return $roster->load(['staff', 'department', 'creator']);
    }

    public function update(Request $request, Roster $roster)
    {
        $data = $request->validate([
            'department_id'  => 'nullable|exists:departments,id',
            'shift_start'    => 'sometimes|required|date_format:H:i',
            'shift_end'      => 'sometimes|required|date_format:H:i',
            'work_days'      => 'nullable|string|max:50',
            'effective_date' => 'sometimes|required|date',
            'end_date'       => 'nullable|date|after_or_equal:effective_date',
            'status'         => 'nullable|in:draft,published,archived',
        ]);

        $roster->update($data);

        return $roster;
    }

    /**
     * Enforces the draft -> published -> archived lifecycle (FR-07).
     */
    public function publish(Roster $roster)
    {
        $roster->update([
            'status'       => 'published',
            'published_at' => now(),
        ]);

        return $roster;
    }

    public function archive(Roster $roster)
    {
        $roster->update(['status' => 'archived']);

        return $roster;
    }

    public function destroy(Roster $roster)
    {
        if ($roster->status === 'published') {
            return response()->json([
                'message' => 'Published rosters cannot be deleted. Archive it first.',
            ], 422);
        }

        $roster->delete();

        return response()->json(null, 204);
    }
}
