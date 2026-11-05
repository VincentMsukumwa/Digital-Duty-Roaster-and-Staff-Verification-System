<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class RoleWebController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')
            ->orderBy('name')
            ->paginate(15);

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:50|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        $role = Role::create($data);

        // Log role creation.
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'ROLE_CREATED',
            'table_affected' => 'roles',
            'record_id'      => $role->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role added.');
    }

    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:50|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);

        $role->update($data);

        // Log role update.
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'ROLE_UPDATED',
            'table_affected' => 'roles',
            'record_id'      => $role->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role updated.');
    }

    public function destroy(Request $request, Role $role)
    {
        $roleId = $role->id;

        try {
            $role->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('roles.index')
                ->with(
                    'status',
                    'Cannot delete this role — one or more users are still assigned to it. Reassign them first.'
                );
        }

        // Log only after successful deletion.
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'ROLE_DELETED',
            'table_affected' => 'roles',
            'record_id'      => $roleId,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role deleted.');
    }
}
