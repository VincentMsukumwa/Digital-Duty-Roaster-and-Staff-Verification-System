<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return Department::withCount('staff')->orderBy('name')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:departments,name',
            'description' => 'nullable|string',
        ]);

        return response()->json(Department::create($data), 201);
    }

    public function show(Department $department)
    {
        return $department->load(['staff', 'rosters']);
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:100|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $department->update($data);

        return $department;
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return response()->json(null, 204);
    }
}
