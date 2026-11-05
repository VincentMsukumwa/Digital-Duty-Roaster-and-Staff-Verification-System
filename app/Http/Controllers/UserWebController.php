<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\UserAccountCreated;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class UserWebController extends Controller
{
    public function index()
    {
        $users = User::with(['staff', 'role'])
            ->orderBy('username')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        // Only staff members without an existing login account.
        $unlinkedStaff = Staff::whereDoesntHave('user')
            ->orderBy('last_name')
            ->get();

        return view('users.create', compact('roles', 'unlinkedStaff'));
    }

   
public function store(Request $request)
{
   
    // Validate the account details entered by the administrator.
    $data = $request->validate([
        'staff_id' => 'nullable|exists:staff,id|unique:users,staff_id',
        'role_id'  => 'nullable|exists:roles,id',
        'username' => 'required|string|max:50|unique:users,username',
        'email'    => 'required|email|max:150|unique:users,email',
    ]);

    // Generate a random initial password.
    // The user will create their own password through the email link.
    $data['password'] = Str::random(64);

    // Create the user account.
    // The User model's hashed cast automatically hashes this password.
    $user = User::create($data);

    // Record account creation in the audit log.
    AuditLog::create([
        'user_id'        => auth()->id(),
        'action'         => 'USER_CREATED',
        'table_affected' => 'users',
        'record_id'      => $user->id,
        'ip_address'     => $request->ip(),
    ]);

    try {
        // Generate a secure, time-limited password setup token.
        $token = Password::broker()->createToken($user);

        // Build the password setup URL.
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        // Send the account creation email through Mailtrap.
        Mail::to($user->email)->send(
            new UserAccountCreated($user, $resetUrl)
        );

        return redirect()
            ->route('users.index')
            ->with(
                'status',
                'User account created successfully. Password setup email sent to ' . $user->email
            );

    } catch (Throwable $e) {

        // Keep the account, but record the email/token delivery problem.
        Log::error('QECH user account email failed.', [
            'user_id' => $user->id,
            'email' => $user->email,
            'error' => $e->getMessage(),
        ]);

        return redirect()
            ->route('users.index')
            ->with(
                'status',
                'User account was created, but the password setup email could not be sent. Check the Laravel log.'
            );
    }
}


    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();

        // Include unlinked staff and the staff member currently linked.
        $unlinkedStaff = Staff::where(function ($query) use ($user) {
            $query->whereDoesntHave('user')
                ->orWhere('id', $user->staff_id);
        })->orderBy('last_name')->get();

        return view('users.edit', compact('user', 'roles', 'unlinkedStaff'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'staff_id' => 'nullable|exists:staff,id|unique:users,staff_id,' . $user->id,
            'role_id'  => 'nullable|exists:roles,id',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email'    => 'required|email|max:150|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        // Record user account update.
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'USER_UPDATED',
            'table_affected' => 'users',
            'record_id'      => $user->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'User account updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('status', "You can't delete your own account while logged in.");
        }

        // Record deletion before deleting the user account.
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'USER_DELETED',
            'table_affected' => 'users',
            'record_id'      => $user->id,
            'ip_address'     => $request->ip(),
        ]);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'User account deleted.');
    }
}
