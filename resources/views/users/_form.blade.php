
@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Username
        </label>
        <input type="text" name="username"
               value="{{ old('username', $user?->username) }}"
               required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Email
        </label>
        <input type="email" name="email"
               value="{{ old('email', $user?->email) }}"
               required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    {{-- Only show password when editing an existing account --}}
    @if ($user)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Password
                <span class="text-gray-400 font-normal">
                    (leave blank to keep unchanged)
                </span>
            </label>

            <input type="password" name="password"
                   autocomplete="new-password"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
    @else
        <div class="md:col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-sm text-blue-800">
                <i class="fas fa-envelope mr-1"></i>
                The system will email the new user a secure link to create
                their own password after the account is created.
            </p>
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Role
        </label>

        <select name="role_id"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

            <option value="">— None —</option>

            @foreach ($roles as $role)
                <option value="{{ $role->id }}"
                    @selected(old('role_id', $user?->role_id) == $role->id)>
                    {{ $role->name }}
                </option>
            @endforeach

        </select>
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Link to Staff Profile
            <span class="text-gray-400">
                (optional — required for a Healthcare Worker to see their own roster)
            </span>
        </label>

        <select name="staff_id"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

            <option value="">— No linked staff profile —</option>

            @foreach ($unlinkedStaff as $staffMember)
                <option value="{{ $staffMember->id }}"
                    @selected(old('staff_id', $user?->staff_id) == $staffMember->id)>
                    {{ $staffMember->first_name }}
                    {{ $staffMember->last_name }}
                    ({{ $staffMember->staff_no }})
                </option>
            @endforeach

        </select>

        @if ($unlinkedStaff->isEmpty())
            <p class="text-xs text-gray-400 mt-1.5">
                No unlinked staff profiles available — every staff member
                already has a login, or none exist yet.
            </p>
        @endif
    </div>

</div>

<div class="mt-6 flex items-center gap-3">

    <button type="submit"
            class="bg-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-blue-700 transition">
        {{ $user ? 'Save Changes' : 'Create User Account' }}
    </button>

    <a href="{{ route('users.index') }}"
       class="text-sm text-gray-500 hover:underline">
        Cancel
    </a>

</div>
