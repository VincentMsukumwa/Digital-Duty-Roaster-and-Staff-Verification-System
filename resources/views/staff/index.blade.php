
@extends('layouts.app')

@section('title', 'Staff')

@section('content')

    {{-- Page Header and Filters --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-4">

        <form method="GET" action="{{ route('staff.index') }}" class="flex items-center gap-3 flex-wrap">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search name or staff no..."
                class="border border-gray-200 rounded-xl px-4 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >

            <select
                name="department_id"
                onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-4 py-2 text-sm"
            >
                <option value="">All Departments</option>

                @foreach ($departments as $department)
                    <option
                        value="{{ $department->id }}"
                        @selected(request('department_id') == $department->id)
                    >
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>

            <select
                name="status"
                onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-4 py-2 text-sm"
            >
                <option value="">All Statuses</option>

                @foreach (['active', 'inactive', 'suspended'] as $option)
                    <option
                        value="{{ $option }}"
                        @selected(request('status') === $option)
                    >
                        {{ ucfirst($option) }}
                    </option>
                @endforeach
            </select>

            <button
                type="submit"
                class="text-sm text-blue-600 hover:underline"
            >
                Filter
            </button>

        </form>

        {{-- Archived Staff and Add Staff --}}
        <div class="flex items-center gap-3 flex-wrap">

            <a
                href="{{ route('staff.archived') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-full border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition"
            >
                Archived Staff
            </a>

            <a
                href="{{ route('staff.create') }}"
                class="bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-blue-700 transition"
            >
                + Add Staff
            </a>

        </div>

    </div>

    {{-- Success Message --}}
    @if (session('status'))
        <div class="bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
            {{ session('status') }}
        </div>
    @endif

    {{-- Error Message --}}
    @if (session('error'))
        <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Staff Table --}}
    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm shadow-blue-100/50 overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left">

                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Staff No</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3">Position</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($staff as $member)

                        @php
                            $hasFingerprint = $member->fingerprintTemplates->isNotEmpty();

                            $isEnrollmentPending = in_array(
                                $member->id,
                                $pendingEnrollmentStaffIds ?? []
                            );
                        @endphp

                        <tr class="hover:bg-blue-50/40">

                            {{-- Staff Number --}}
                            <td class="px-4 py-3">
                                {{ $member->staff_no }}
                            </td>

                            {{-- Staff Name and Statistics --}}
                            <td class="px-4 py-3 font-medium text-gray-800">

                                <button
                                    type="button"
                                    class="text-blue-700 hover:text-blue-900 hover:underline"
                                    onclick="openStaffStatistics(
                                        {{ $member->id }},
                                        {{ Js::from($member->first_name . ' ' . $member->last_name) }},
                                        {{ Js::from($member->staff_no) }}
                                    )"
                                >
                                    {{ $member->first_name }} {{ $member->last_name }}
                                </button>

                            </td>

                            {{-- Department --}}
                            <td class="px-4 py-3">
                                {{ $member->department->name ?? '—' }}
                            </td>

                            {{-- Position --}}
                            <td class="px-4 py-3">
                                {{ $member->position ?? '—' }}
                            </td>

                            {{-- Staff Status --}}
                            <td class="px-4 py-3">

                                <span @class([
                                    'px-3 py-1 rounded-full text-xs font-medium',
                                    'bg-green-100 text-green-700' => $member->status === 'active',
                                    'bg-gray-100 text-gray-600' => $member->status === 'inactive',
                                    'bg-red-100 text-red-700' => $member->status === 'suspended',
                                ])>
                                    {{ ucfirst($member->status) }}
                                </span>

                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-3">

                                {{-- Edit --}}
                                <a
                                    href="{{ route('staff.edit', $member) }}"
                                    class="text-blue-600 hover:underline"
                                >
                                    Edit
                                </a>

                                {{-- Fingerprint Registration Status --}}
                                @if ($hasFingerprint)

                                    <span class="text-green-700 font-medium">
                                        <i class="fas fa-fingerprint"></i>
                                        Fingerprint Registered
                                    </span>

                                @elseif ($isEnrollmentPending)

                                    <span class="text-amber-600 font-medium">
                                        Enrollment Pending
                                    </span>

                                @else

                                    <form
                                        method="POST"
                                        action="{{ route('staff.fingerprint.start', $member) }}"
                                        class="inline"
                                        onsubmit="return confirm('Start fingerprint registration for {{ $member->first_name }} {{ $member->last_name }}?');"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="text-green-600 hover:underline"
                                        >
                                            Register Fingerprint
                                        </button>

                                    </form>

                                @endif

                                {{-- Archive --}}
                                <form
                                    method="POST"
                                    action="{{ route('staff.destroy', $member) }}"
                                    class="inline"
                                    onsubmit="return confirm('Archive {{ $member->first_name }} {{ $member->last_name }}? You can restore this staff member later.');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-amber-600 hover:underline"
                                    >
                                        Archive
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                No staff members found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $staff->links() }}
    </div>

    {{-- Individual Staff Attendance Statistics Modal --}}
    <div
        id="staffStatisticsModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="staffStatisticsTitle"
        onclick="if (event.target === this) closeStaffStatistics()"
    >

        <div class="w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-2xl">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 p-6">

                <div>
                    <h2 id="staffStatisticsTitle" class="text-xl font-bold text-gray-800">
                        Staff Attendance Statistics
                    </h2>

                    <p id="staffStatisticsIdentity" class="mt-1 text-sm text-gray-500"></p>
                </div>

                <button
                    type="button"
                    onclick="closeStaffStatistics()"
                    class="rounded-lg px-3 py-2 text-2xl text-gray-400 hover:bg-gray-100 hover:text-gray-700"
                    aria-label="Close statistics"
                >
                    &times;
                </button>

            </div>

            <div class="space-y-6 p-6">

                {{-- Date Range Filters --}}
                <form id="staffStatisticsFilter" class="flex flex-wrap items-end gap-3">

                    <div>

                        <label for="statisticsPeriod" class="mb-1 block text-sm font-medium text-gray-600">
                            Reporting period
                        </label>

                        <select
                            id="statisticsPeriod"
                            name="period"
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                        >
                            <option value="week">This week</option>
                            <option value="month" selected>This month</option>
                            <option value="custom">Custom date range</option>
                        </select>

                    </div>

                    <div id="customDateFields" class="hidden flex-wrap gap-3">

                        <div>

                            <label for="statisticsStartDate" class="mb-1 block text-sm font-medium text-gray-600">
                                From
                            </label>

                            <input
                                type="date"
                                id="statisticsStartDate"
                                name="start_date"
                                class="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                            >

                        </div>

                        <div>

                            <label for="statisticsEndDate" class="mb-1 block text-sm font-medium text-gray-600">
                                To
                            </label>

                            <input
                                type="date"
                                id="statisticsEndDate"
                                name="end_date"
                                class="rounded-lg border border-gray-200 px-3 py-2 text-sm"
                            >

                        </div>

                    </div>

                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Apply Filters
                    </button>

                </form>

                {{-- Loading / Error Message --}}
                <p id="staffStatisticsMessage" class="text-sm text-gray-500">
                    Select a reporting period to view attendance statistics.
                </p>

                {{-- Statistics Cards --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-sm text-gray-500">Attendance records</p>
                        <p id="statisticsTotal" class="mt-2 text-2xl font-bold text-gray-800">—</p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-sm text-gray-500">Late arrivals</p>
                        <p id="statisticsLate" class="mt-2 text-2xl font-bold text-amber-600">—</p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-sm text-gray-500">Early departures</p>
                        <p id="statisticsEarly" class="mt-2 text-2xl font-bold text-red-600">—</p>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-sm text-gray-500">Attendance days</p>
                        <p id="statisticsDays" class="mt-2 text-2xl font-bold text-green-600">—</p>
                    </div>

                </div>

                {{-- Attendance History --}}
                <div>

                    <h3 class="mb-3 font-semibold text-gray-800">
                        Attendance History
                    </h3>

                    <div class="overflow-x-auto rounded-xl border border-gray-100">

                        <table class="w-full text-left text-sm">

                            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Time</th>
                                    <th class="px-4 py-3">Scan</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>

                            <tbody id="staffStatisticsHistory" class="divide-y divide-gray-100">

                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                        Attendance records will appear here.
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

<script>
    let selectedStatisticsStaffId = null;
    let statisticsRequestController = null;

    function openStaffStatistics(staffId, staffName, staffNumber) {

        selectedStatisticsStaffId = staffId;

        document.getElementById('staffStatisticsIdentity').textContent =
            staffName + ' · Staff No: ' + staffNumber;

        const modal = document.getElementById('staffStatisticsModal');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Default to the current month.
        document.getElementById('statisticsPeriod').value = 'month';

        document.getElementById('customDateFields').classList.add('hidden');
        document.getElementById('customDateFields').classList.remove('flex');

        loadStaffStatistics();
    }

    function closeStaffStatistics() {

        const modal = document.getElementById('staffStatisticsModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        selectedStatisticsStaffId = null;

        if (statisticsRequestController) {
            statisticsRequestController.abort();
            statisticsRequestController = null;
        }
    }

    async function loadStaffStatistics() {

        if (!selectedStatisticsStaffId) {
            return;
        }

        // Cancel an older request if the user changes filters quickly.
        if (statisticsRequestController) {
            statisticsRequestController.abort();
        }

        const requestController = new AbortController();
        statisticsRequestController = requestController;

        const staffId = selectedStatisticsStaffId;
        const period = document.getElementById('statisticsPeriod').value;
        const message = document.getElementById('staffStatisticsMessage');
        const history = document.getElementById('staffStatisticsHistory');

        const params = new URLSearchParams({ period });

        if (period === 'custom') {

            const startDate = document.getElementById('statisticsStartDate').value;
            const endDate = document.getElementById('statisticsEndDate').value;

            if (!startDate || !endDate) {
                message.textContent = 'Please select both the start and end dates.';
                return;
            }

            if (startDate > endDate) {
                message.textContent = 'The start date cannot be after the end date.';
                return;
            }

            params.set('start_date', startDate);
            params.set('end_date', endDate);
        }

        document.getElementById('statisticsTotal').textContent = '—';
        document.getElementById('statisticsLate').textContent = '—';
        document.getElementById('statisticsEarly').textContent = '—';
        document.getElementById('statisticsDays').textContent = '—';

        history.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                    Loading attendance records...
                </td>
            </tr>
        `;

        message.textContent = 'Loading attendance statistics...';

        try {

            const response = await fetch(
                `/staff/${staffId}/statistics?${params.toString()}`,
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: requestController.signal
                }
            );

            const result = await response.json();

            if (!response.ok) {
                throw new Error(
                    result.message || 'Unable to load attendance statistics.'
                );
            }

            // Ignore a response if the selected staff member changed.
            if (
                !selectedStatisticsStaffId ||
                Number(result.staff.id) !== Number(selectedStatisticsStaffId)
            ) {
                return;
            }

            document.getElementById('statisticsTotal').textContent =
                result.statistics.total_scans;

            document.getElementById('statisticsLate').textContent =
                result.statistics.late_arrivals;

            document.getElementById('statisticsEarly').textContent =
                result.statistics.early_departures;

            document.getElementById('statisticsDays').textContent =
                result.statistics.attendance_days;

            if (result.records.length === 0) {

                history.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            No attendance records found for this period.
                        </td>
                    </tr>
                `;

            } else {

                history.innerHTML = result.records.map(record => `
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3">${escapeStatisticsText(record.date)}</td>
                        <td class="px-4 py-3">${escapeStatisticsText(record.time)}</td>
                        <td class="px-4 py-3">${escapeStatisticsText(record.scan_type)}</td>
                        <td class="px-4 py-3">${escapeStatisticsText(record.status)}</td>
                    </tr>
                `).join('');
            }

            message.textContent =
                `Showing ${result.period.start_date} to ${result.period.end_date}.`;

        } catch (error) {

            if (error.name === 'AbortError') {
                return;
            }

            console.error('Staff statistics error:', error);

            message.textContent =
                error.message || 'Something went wrong while loading statistics.';

            history.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-red-600">
                        Failed to load attendance records. Please try again.
                    </td>
                </tr>
            `;

        } finally {

            if (statisticsRequestController === requestController) {
                statisticsRequestController = null;
            }
        }
    }

    // Escape database text before inserting it into the history table.
    function escapeStatisticsText(value) {

        const element = document.createElement('span');

        element.textContent = value == null ? '' : String(value);

        return element.innerHTML;
    }

    document.getElementById('statisticsPeriod').addEventListener('change', function () {

        const customFields = document.getElementById('customDateFields');

        if (this.value === 'custom') {
            customFields.classList.remove('hidden');
            customFields.classList.add('flex');
        } else {
            customFields.classList.add('hidden');
            customFields.classList.remove('flex');
        }
    });

    document.getElementById('staffStatisticsFilter').addEventListener('submit', function (event) {
        event.preventDefault();
        loadStaffStatistics();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeStaffStatistics();
        }
    });
</script>

@endsection
