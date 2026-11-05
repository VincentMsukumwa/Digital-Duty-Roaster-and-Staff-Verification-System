<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return User::with(['staff', 'role'])->orderBy('username')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_id' => 'nullable|exists:staff,id|unique:users,staff_id',
            'role_id'  => 'nullable|exists:roles,id',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        // The 'hashed' cast on the User model hashes this automatically on save.
        return response()->json(User::create($data), 201);
    }

    public function show(User $user)
    {
        return $user->load(['staff', 'role']);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'staff_id' => 'nullable|exists:staff,id|unique:users,staff_id,' . $user->id,
            'role_id'  => 'nullable|exists:roles,id',
            'username' => 'sometimes|required|string|max:50|unique:users,username,' . $user->id,
            'email'    => 'sometimes|required|email|max:150|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return $user;
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(null, 204);
    }
}
