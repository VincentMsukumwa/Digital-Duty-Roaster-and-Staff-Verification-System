<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return Role::withCount('users')->orderBy('name')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:50|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        return response()->json(Role::create($data), 201);
    }

    public function show(Role $role)
    {
        return $role->loadCount('users');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:50|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);

        $role->update($data);

        return $role;
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(null, 204);
    }
}
