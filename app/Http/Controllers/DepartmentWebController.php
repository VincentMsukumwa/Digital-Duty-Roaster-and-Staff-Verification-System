<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentWebController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('staff')
            ->orderBy('name')
            ->paginate(15);

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:departments,name',
            'description' => 'nullable|string',
        ]);

        $department = Department::create($data);

        // Record department creation.
        AuditLog::create([
            'user_id'        => $request->user()?->id,
            'action'         => 'DEPARTMENT_CREATED',
            'table_affected' => 'departments',
            'record_id'      => $department->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('departments.index')
            ->with('status', 'Department added.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $department->update($data);

        // Record department update.
        AuditLog::create([
            'user_id'        => $request->user()?->id,
            'action'         => 'DEPARTMENT_UPDATED',
            'table_affected' => 'departments',
            'record_id'      => $department->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('departments.index')
            ->with('status', 'Department updated.');
    }

    public function destroy(Request $request, Department $department)
    {
        $departmentId = $department->id;

        // Record department deletion before deleting the record.
        AuditLog::create([
            'user_id'        => $request->user()?->id,
            'action'         => 'DEPARTMENT_DELETED',
            'table_affected' => 'departments',
            'record_id'      => $departmentId,
            'ip_address'     => $request->ip(),
        ]);

        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('status', 'Department deleted.');
    }
}
