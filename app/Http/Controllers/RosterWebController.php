<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Roster;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RosterWebController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role->name ?? null;

        $query = Roster::with(['staff', 'department', 'creator'])
            ->orderByDesc('effective_date');

        $noStaffLink = false;

        if ($role === 'Healthcare Worker') {
            if ($user->staff_id) {
                $query->where('staff_id', $user->staff_id);
            } else {
                $query->whereRaw('1 = 0');
                $noStaffLink = true;
            }
        } else {
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rosters = $query->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('rosters.index', compact(
            'rosters',
            'departments',
            'noStaffLink'
        ));
    }

    public function create()
    {
        $staffList = Staff::where('status', 'active')
            ->orderBy('last_name')
            ->get();

        $departments = Department::orderBy('name')->get();

        return view('rosters.create', compact('staffList', 'departments'));
    }

    /**
     * Create an individual roster.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['created_by'] = $request->user()->id;
        $data['status'] = 'draft';

        $roster = Roster::create($data);

        AuditLog::create([
            'user_id'        => $request->user()->id,
            'action'         => 'ROSTER_CREATED',
            'table_affected' => 'rosters',
            'record_id'      => $roster->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('rosters.index')
            ->with('status', 'Roster created as draft.');
    }

    public function edit(Roster $roster)
    {
        $staffList = Staff::where('status', 'active')
            ->orderBy('last_name')
            ->get();

        $departments = Department::orderBy('name')->get();

        return view('rosters.edit', compact('roster', 'staffList', 'departments'));
    }

    /**
     * Update an existing roster.
     */
    public function update(Request $request, Roster $roster)
    {
        $data = $this->validated($request);

        $roster->update($data);

        AuditLog::create([
            'user_id'        => $request->user()->id,
            'action'         => 'ROSTER_UPDATED',
            'table_affected' => 'rosters',
            'record_id'      => $roster->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('rosters.index')
            ->with('status', 'Roster updated.');
    }

    /**
     * Delete a roster.
     */
    public function destroy(Request $request, Roster $roster)
    {
        if ($roster->status === 'published') {
            return redirect()
                ->route('rosters.index')
                ->with('status', 'Published rosters cannot be deleted — archive it first.');
        }

        $rosterId = $roster->id;

        AuditLog::create([
            'user_id'        => $request->user()->id,
            'action'         => 'ROSTER_DELETED',
            'table_affected' => 'rosters',
            'record_id'      => $rosterId,
            'ip_address'     => $request->ip(),
        ]);

        $roster->delete();

        return redirect()
            ->route('rosters.index')
            ->with('status', 'Roster deleted.');
    }

    /**
     * Publish a roster.
     */
    public function publish(Request $request, Roster $roster)
    {
        $roster->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        AuditLog::create([
            'user_id'        => $request->user()->id,
            'action'         => 'ROSTER_PUBLISHED',
            'table_affected' => 'rosters',
            'record_id'      => $roster->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('rosters.index')
            ->with('status', 'Roster published.');
    }

    /**
     * Archive a roster.
     */
    public function archive(Request $request, Roster $roster)
    {
        $roster->update([
            'status' => 'archived',
        ]);

        AuditLog::create([
            'user_id'        => $request->user()->id,
            'action'         => 'ROSTER_ARCHIVED',
            'table_affected' => 'rosters',
            'record_id'      => $roster->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('rosters.index')
            ->with('status', 'Roster archived.');
    }

    /**
     * Show the bulk roster creation page.
     */
    public function bulkCreate()
    {
        $departments = Department::orderBy('name')->get();

        return view('rosters.bulk-create', compact('departments'));
    }

    /**
     * Return active staff belonging to the selected department.
     */
    public function bulkStaff(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
        ]);

        $staff = Staff::where('department_id', $data['department_id'])
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get([
                'id',
                'first_name',
                'last_name',
                'staff_no',
            ]);

        return response()->json($staff);
    }

    /**
     * Create draft rosters for multiple staff members.
     */
    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'department_id'  => 'required|exists:departments,id',

            'staff_ids'      => 'required|array|min:1',
            'staff_ids.*'    => 'required|integer|distinct|exists:staff,id',

            'shift_start'    => 'required|date_format:H:i',
            'shift_end'      => 'required|date_format:H:i',

            'effective_date' => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:effective_date',

            'work_days'      => 'required|array|min:1',
            'work_days.*'    => 'required|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
        ]);

        // Only active staff from the selected department are allowed.
        $staffMembers = Staff::whereIn('id', $data['staff_ids'])
            ->where('department_id', $data['department_id'])
            ->where('status', 'active')
            ->get();

        if ($staffMembers->count() !== count($data['staff_ids'])) {
            throw ValidationException::withMessages([
                'staff_ids' => 'Some selected staff are inactive or do not belong to the selected department.',
            ]);
        }

        $workDays = array_values(array_unique($data['work_days']));

        // Prevent overlapping roster schedules.
        foreach ($staffMembers as $member) {
            $existingRosters = Roster::where('staff_id', $member->id)
                ->whereIn('status', ['draft', 'published'])
                ->whereDate('effective_date', '<=', $data['end_date'] ?? '9999-12-31')
                ->where(function ($query) use ($data) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $data['effective_date']);
                })
                ->get();

            foreach ($existingRosters as $existing) {
                $existingDays = array_filter(
                    explode(',', $existing->work_days ?? '')
                );

                $sameDay = count(array_intersect($workDays, $existingDays)) > 0;

                if (! $sameDay) {
                    continue;
                }

                $newStart = strtotime($data['shift_start']);
                $newEnd = strtotime($data['shift_end']);
                $oldStart = strtotime(substr($existing->shift_start, 0, 5));
                $oldEnd = strtotime(substr($existing->shift_end, 0, 5));

                // An end time at or before the start time means an overnight shift.
                if ($newEnd <= $newStart) {
                    $newEnd += 86400;
                }

                if ($oldEnd <= $oldStart) {
                    $oldEnd += 86400;
                }

                if ($newStart < $oldEnd && $oldStart < $newEnd) {
                    throw ValidationException::withMessages([
                        'staff_ids' => "A conflicting roster already exists for {$member->first_name} {$member->last_name}. Adjust the staff selection, dates, or shift.",
                    ]);
                }
            }
        }

        // Create all rosters and their audit records together.
        DB::transaction(function () use ($data, $staffMembers, $workDays, $request) {

            foreach ($staffMembers as $member) {

                $roster = Roster::create([
                    'staff_id'       => $member->id,
                    'department_id'  => $data['department_id'],
                    'shift_start'    => $data['shift_start'],
                    'shift_end'      => $data['shift_end'],
                    'work_days'      => implode(',', $workDays),
                    'effective_date' => $data['effective_date'],
                    'end_date'       => $data['end_date'] ?? null,
                    'created_by'     => $request->user()->id,
                    'status'         => 'draft',
                ]);

                AuditLog::create([
                    'user_id'        => $request->user()->id,
                    'action'         => 'ROSTER_BULK_CREATED',
                    'table_affected' => 'rosters',
                    'record_id'      => $roster->id,
                    'ip_address'     => $request->ip(),
                ]);
            }
        });

        return redirect()
            ->route('rosters.index')
            ->with('status', count($data['staff_ids']) . ' draft rosters created successfully.');
    }

    /**
     * Shared validation + converts work_days into a comma-separated string.
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'staff_id'       => 'required|exists:staff,id',
            'department_id'  => 'nullable|exists:departments,id',
            'shift_start'    => 'required|date_format:H:i',
            'shift_end'      => 'required|date_format:H:i',
            'work_days'      => 'nullable|array',
            'work_days.*'    => 'string',
            'effective_date' => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:effective_date',
        ]);

        $data['work_days'] = implode(',', $data['work_days'] ?? []);

        return $data;
    }
}
