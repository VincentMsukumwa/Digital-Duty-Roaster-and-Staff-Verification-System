
@extends('layouts.app')

@section('title', 'Bulk Roster Scheduling')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Bulk Roster Scheduling</h1>
        <p class="text-sm text-gray-500 mt-1">
            Assign a common shift to multiple staff members in one operation.
        </p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4">
            <p class="font-semibold mb-2">Please correct the following:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('rosters.bulk.store') }}"
          class="bg-white border border-gray-200 rounded-2xl p-6 md:p-8 space-y-6">
        @csrf

        <div>
            <label for="department_id" class="block text-sm font-semibold mb-2">
                1. Select Department
            </label>

            <select id="department_id" name="department_id" required
                    class="w-full border border-gray-300 rounded-xl px-4 py-3">
                <option value="">Choose a department</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}"
                        @selected(old('department_id') == $department->id)>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="space-y-3">
            <div class="flex flex-wrap justify-between items-center gap-3">
                <div>
                    <h2 class="font-semibold">2. Select Staff Members</h2>
                    <p class="text-sm text-gray-500">
                        Choose everyone or select specific staff.
                    </p>
                </div>

                <span id="selected-count"
                      class="text-sm font-semibold text-blue-700">
                    0 selected
                </span>
            </div>

            <input id="staff-search" type="search"
                   placeholder="Search by name or staff number..."
                   class="w-full border border-gray-300 rounded-xl px-4 py-3">

            <div class="flex flex-wrap gap-3">
                <button type="button" id="select-all"
                        class="text-sm font-semibold text-blue-700 hover:underline">
                    Select all
                </button>
                <button type="button" id="clear-all"
                        class="text-sm font-semibold text-gray-600 hover:underline">
                    Clear selection
                </button>
            </div>

            <div id="staff-list"
                 class="border border-gray-200 rounded-xl divide-y divide-gray-100 max-h-80 overflow-y-auto">
                <p class="p-4 text-sm text-gray-500">
                    Select a department to load its active staff.
                </p>
            </div>

            <p class="text-xs text-gray-500">
                Staff selection is limited to active members of the chosen department.
            </p>
        </div>

        <div class="border-t border-gray-100 pt-6 space-y-5">
            <h2 class="font-semibold">3. Configure the Shared Shift</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="shift_start" class="block text-sm font-medium mb-2">
                        Shift Start
                    </label>
                    <input id="shift_start" type="time" name="shift_start"
                           value="{{ old('shift_start') }}" required
                           class="w-full border border-gray-300 rounded-xl px-4 py-3">
                </div>

                <div>
                    <label for="shift_end" class="block text-sm font-medium mb-2">
                        Shift End
                    </label>
                    <input id="shift_end" type="time" name="shift_end"
                           value="{{ old('shift_end') }}" required
                           class="w-full border border-gray-300 rounded-xl px-4 py-3">
                </div>

                <div>
                    <label for="effective_date" class="block text-sm font-medium mb-2">
                        Effective Date
                    </label>
                    <input id="effective_date" type="date" name="effective_date"
                           value="{{ old('effective_date') }}" required
                           class="w-full border border-gray-300 rounded-xl px-4 py-3">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium mb-2">
                        End Date (optional)
                    </label>
                    <input id="end_date" type="date" name="end_date"
                           value="{{ old('end_date') }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-3">
                </div>
            </div>

            <div>
                <p class="block text-sm font-medium mb-3">Working Days</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                        <label class="flex items-center gap-2 border border-gray-200 rounded-full px-3 py-2 text-sm cursor-pointer">
                            <input type="checkbox" name="work_days[]"
                                   value="{{ $day }}"
                                   @checked(in_array($day, old('work_days', [])))>
                            {{ $day }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 text-white font-semibold rounded-xl px-6 py-3 hover:bg-blue-700">
                Create Selected Rosters
            </button>

            <a href="{{ route('rosters.index') }}"
               class="border border-gray-300 rounded-xl px-6 py-3 text-sm font-semibold">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const departmentSelect = document.getElementById('department_id');
    const staffList = document.getElementById('staff-list');
    const searchInput = document.getElementById('staff-search');
    const selectedCount = document.getElementById('selected-count');
    const selectAllButton = document.getElementById('select-all');
    const clearAllButton = document.getElementById('clear-all');

    const staffEndpoint = @json(route('rosters.bulk.staff'));
    const previouslySelected = @json(old('staff_ids', []));
    let staffMembers = [];
    let loading = false;

    function updateCount() {
        const checked = staffList.querySelectorAll(
            'input[name="staff_ids[]"]:checked'
        ).length;

        selectedCount.textContent = `${checked} selected`;
    }

    function renderStaff() {
        const search = searchInput.value.trim().toLowerCase();

        const filtered = staffMembers.filter(member => {
            const text = `${member.first_name} ${member.last_name} ${member.staff_no ?? ''}`.toLowerCase();
            return text.includes(search);
        });

        staffList.replaceChildren();

        if (filtered.length === 0) {
            const message = document.createElement('p');
            message.className = 'p-4 text-sm text-gray-500';
            message.textContent = staffMembers.length
                ? 'No staff match your search.'
                : 'No active staff found in this department.';
            staffList.appendChild(message);
            updateCount();
            return;
        }

        filtered.forEach(member => {
            const label = document.createElement('label');
            label.className = 'flex items-center gap-3 p-3 hover:bg-blue-50 cursor-pointer';

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'staff_ids[]';
            checkbox.value = member.id;
            checkbox.checked = previouslySelected.map(String).includes(String(member.id));
            checkbox.className = 'rounded border-gray-300';

            checkbox.addEventListener('change', updateCount);

            const details = document.createElement('span');
            details.className = 'text-sm text-gray-800';
            details.textContent = `${member.first_name} ${member.last_name} (${member.staff_no ?? 'No staff number'})`;

            label.append(checkbox, details);
            staffList.appendChild(label);
        });

        updateCount();
    }

    async function loadStaff() {
        const departmentId = departmentSelect.value;

        staffMembers = [];
        previouslySelected.splice(0, previouslySelected.length);
        staffList.replaceChildren();

        if (!departmentId) {
            const message = document.createElement('p');
            message.className = 'p-4 text-sm text-gray-500';
            message.textContent = 'Select a department to load its active staff.';
            staffList.appendChild(message);
            updateCount();
            return;
        }

        loading = true;
        const message = document.createElement('p');
        message.className = 'p-4 text-sm text-gray-500';
        message.textContent = 'Loading staff...';
        staffList.replaceChildren(message);

        try {
            const response = await fetch(
                `${staffEndpoint}?department_id=${encodeURIComponent(departmentId)}`,
                { headers: { 'Accept': 'application/json' } }
            );

            if (!response.ok) {
                throw new Error('Unable to load staff.');
            }

            staffMembers = await response.json();
            renderStaff();
        } catch (error) {
            staffList.replaceChildren();
            const errorMessage = document.createElement('p');
            errorMessage.className = 'p-4 text-sm text-red-600';
            errorMessage.textContent = 'Could not load staff. Please refresh and try again.';
            staffList.appendChild(errorMessage);
        } finally {
            loading = false;
        }
    }

    departmentSelect.addEventListener('change', loadStaff);
    searchInput.addEventListener('input', renderStaff);

    selectAllButton.addEventListener('click', () => {
        staffList.querySelectorAll('input[name="staff_ids[]"]').forEach(input => {
            input.checked = true;
        });
        updateCount();
    });

    clearAllButton.addEventListener('click', () => {
        staffList.querySelectorAll('input[name="staff_ids[]"]').forEach(input => {
            input.checked = false;
        });
        updateCount();
    });

    if (departmentSelect.value) {
        loadStaff();
    }
});
</script>
@endsection
