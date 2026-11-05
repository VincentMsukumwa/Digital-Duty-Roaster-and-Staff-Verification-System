@php
    $selectedDays = old('work_days', $roster?->work_days ? explode(',', $roster->work_days) : []);
    $shiftStartValue = old('shift_start', $roster?->shift_start ? \Carbon\Carbon::parse($roster->shift_start)->format('H:i') : '');
    $shiftEndValue = old('shift_end', $roster?->shift_end ? \Carbon\Carbon::parse($roster->shift_end)->format('H:i') : '');
@endphp

@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Staff Member</label>
        <select name="staff_id" required
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">— Select —</option>
            @foreach ($staffList as $member)
                <option value="{{ $member->id }}" @selected(old('staff_id', $roster?->staff_id) == $member->id)>
                    {{ $member->first_name }} {{ $member->last_name }} ({{ $member->staff_no }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
        <select name="department_id"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">— None —</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $roster?->department_id) == $department->id)>
                    {{ $department->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Shift Start</label>
        <input type="time" name="shift_start" value="{{ $shiftStartValue }}" required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Shift End</label>
        <input type="time" name="shift_end" value="{{ $shiftEndValue }}" required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Effective Date</label>
        <input type="date" name="effective_date" value="{{ old('effective_date', $roster?->effective_date?->format('Y-m-d')) }}" required
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">End Date <span class="text-gray-400">(optional)</span></label>
        <input type="date" name="end_date" value="{{ old('end_date', $roster?->end_date?->format('Y-m-d')) }}"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Work Days</label>
        <div class="flex flex-wrap gap-2">
            @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                <label class="flex items-center gap-1.5 text-sm bg-gray-50 border border-gray-200 rounded-full px-3 py-1.5 cursor-pointer has-[:checked]:bg-blue-50 has-[:checked]:border-blue-200 has-[:checked]:text-blue-700">
                    <input type="checkbox" name="work_days[]" value="{{ $day }}"
                           @checked(in_array($day, $selectedDays))
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    {{ $day }}
                </label>
            @endforeach
        </div>
    </div>

</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="bg-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-blue-700 transition">
        {{ $roster ? 'Save Changes' : 'Create Roster (as Draft)' }}
    </button>
    <a href="{{ route('rosters.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
</div>
