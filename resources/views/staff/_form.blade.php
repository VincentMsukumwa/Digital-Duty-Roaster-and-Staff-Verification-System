@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Staff Number</label>
        <input type="text" name="staff_no" value="{{ old('staff_no', $staff?->staff_no) }}" required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
        <select name="department_id"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">— None —</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}"
                    @selected(old('department_id', $staff?->department_id) == $department->id)>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">First Name</label>
        <input type="text" name="first_name" value="{{ old('first_name', $staff?->first_name) }}" required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Last Name</label>
        <input type="text" name="last_name" value="{{ old('last_name', $staff?->last_name) }}" required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
        <select name="gender"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">— Select —</option>
            @foreach (['Male', 'Female', 'Other'] as $option)
                <option value="{{ $option }}" @selected(old('gender', $staff?->gender) === $option)>{{ $option }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $staff?->phone) }}"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
        <input type="email" name="email" value="{{ old('email', $staff?->email) }}"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Position</label>
        <input type="text" name="position" value="{{ old('position', $staff?->position) }}"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Date Hired</label>
        <input type="date" name="date_hired" value="{{ old('date_hired', $staff?->date_hired?->format('Y-m-d')) }}"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
        <select name="status"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            @foreach (['active', 'inactive', 'suspended'] as $option)
                <option value="{{ $option }}" @selected(old('status', $staff?->status ?? 'active') === $option)>
                    {{ ucfirst($option) }}
                </option>
            @endforeach
        </select>
    </div>

</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="bg-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-blue-700 transition">
        {{ $staff ? 'Save Changes' : 'Add Staff Member' }}
    </button>
    <a href="{{ route('staff.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
</div>
