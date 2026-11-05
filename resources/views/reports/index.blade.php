@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Reports</h1>
            <p class="text-sm text-gray-500 mt-1">
                Attendance and duty-roster reporting
            </p>
        </div>

        <button onclick="window.print()"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-800 px-4 py-2 text-white hover:bg-gray-700">
            Print Report
        </button>
    </div>

    {{-- Report filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" action="{{ route('reports.index') }}"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            <div>
                <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">
                    Report Type
                </label>
                <select name="report_type" id="report_type"
                    class="w-full rounded-lg border-gray-300">
                    <option value="daily" @selected($reportType === 'daily')>
                        Daily Attendance
                    </option>
                    <option value="department" @selected($reportType === 'department')>
                        Department Attendance
                    </option>
                    <option value="staff" @selected($reportType === 'staff')>
                        Staff Attendance History
                    </option>
                    <option value="roster" @selected($reportType === 'roster')>
                        Duty-Roster Report
                    </option>
                    <option value="exceptions" @selected($reportType === 'exceptions')>
                        Attendance Exceptions
                    </option>
                </select>
            </div>

            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">
                    Report Date
                </label>
                <input type="date" name="date" id="date"
                    value="{{ $date }}"
                    class="w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                    Start Date
                </label>
                <input type="date" name="start_date" id="start_date"
                    value="{{ $startDate }}"
                    class="w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                    End Date
                </label>
                <input type="date" name="end_date" id="end_date"
                    value="{{ $endDate }}"
                    class="w-full rounded-lg border-gray-300">
            </div>

            @if($canViewAll)
                <div>
                    <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Department
                    </label>
                    <select name="department_id" id="department_id"
                        class="w-full rounded-lg border-gray-300">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}"
                                @selected((string) ($filters['department_id'] ?? '') === (string) $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Staff Member
                    </label>
                    <select name="staff_id" id="staff_id"
                        class="w-full rounded-lg border-gray-300">
                        <option value="">All Staff</option>
                        @foreach($staffMembers as $member)
                            <option value="{{ $member->id }}"
                                @selected((string) ($filters['staff_id'] ?? '') === (string) $member->id)>
                                {{ $member->full_name }} ({{ $member->staff_no }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap gap-3">
                <button type="submit"
                    class="rounded-lg bg-blue-700 px-5 py-2 text-white hover:bg-blue-800">
                    Generate Report
                </button>

                <a href="{{ route('reports.index') }}"
                    class="rounded-lg border border-gray-300 px-5 py-2 text-gray-700 hover:bg-gray-50">
                    Reset Filters
                </a>
            </div>
        </form>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Attendance Records</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">
                {{ $totalRecords }}
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Staff Recorded</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">
                {{ $totalStaffPresent }}
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Late Arrivals</p>
            <p class="text-3xl font-bold text-amber-600 mt-2">
                {{ $lateArrivals }}
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <p class="text-sm text-gray-500">Early Departures</p>
            <p class="text-3xl font-bold text-red-600 mt-2">
                {{ $earlyDepartures }}
            </p>
        </div>
    </div>

    {{-- Attendance report --}}
    @if($reportType !== 'roster')
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200">
                <h2 class="font-semibold text-lg text-gray-800">
                    Attendance Records
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $reportType === 'daily'
                        ? 'Attendance for ' . $date
                        : 'Period: ' . $startDate . ' to ' . $endDate }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Staff</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Department</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Scan Time</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Type</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Device</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($attendanceRecords as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-sm whitespace-nowrap">
                                    {{ $record->scan_date?->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    {{ $record->staff?->full_name ?? 'Unknown Staff' }}
                                    <div class="text-xs text-gray-500">
                                        {{ $record->staff?->staff_no ?? '—' }}
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    {{ $record->staff?->department?->name ?? 'Unassigned' }}
                                </td>
                                <td class="px-5 py-3 text-sm whitespace-nowrap">
                                    {{ $record->scan_time }}
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    {{ strtoupper($record->scan_type) }}
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    {{ str_replace('_', ' ', ucfirst($record->status)) }}
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    {{ $record->device?->device_name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                    No attendance records match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Duty-roster report --}}
    @if($reportType === 'roster')
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200">
                <h2 class="font-semibold text-lg text-gray-800">Duty-Roster Report</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Period: {{ $startDate }} to {{ $endDate }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Staff</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Department</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Shift Start</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Shift End</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Effective Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">End Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($rosters as $roster)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-sm">
                                    {{ $roster->staff?->full_name ?? 'Unknown Staff' }}
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    {{ $roster->department?->name ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-sm whitespace-nowrap">
                                    {{ $roster->shift_start }}
                                </td>
                                <td class="px-5 py-3 text-sm whitespace-nowrap">
                                    {{ $roster->shift_end }}
                                </td>
                                <td class="px-5 py-3 text-sm whitespace-nowrap">
                                    {{ $roster->effective_date?->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 text-sm whitespace-nowrap">
                                    {{ $roster->end_date?->format('d M Y') ?? 'Ongoing' }}
                                </td>
                                <td class="px-5 py-3 text-sm">
                                    {{ ucfirst($roster->status) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                    No roster assignments match these filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <p class="text-xs text-gray-500">
        Generated on {{ now()->format('d M Y, H:i') }}.
        Summary counts are based on the attendance records displayed.
    </p>
</div>

<style>
    @media print {
        nav, aside, button, form, a {
            display: none !important;
        }

        body {
            background: white !important;
        }

        main {
            padding: 0 !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }

        .overflow-x-auto {
            overflow: visible !important;
        }

        table {
            width: 100% !important;
            font-size: 10px !important;
        }

        th, td {
            padding: 6px !important;
        }
    }
</style>
@endsection
