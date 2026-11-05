
@extends('layouts.app')

@section('title', 'Staff Attendance History')

@section('content')
<div class="space-y-6">

    {{-- Staff profile header --}}
    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm p-6">
        <a href="{{ route('rosters.index') }}"
           class="text-sm text-blue-600 hover:underline">
            &larr; Back to Duty Rosters
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">
                    {{ $staff->first_name }} {{ $staff->last_name }}
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Staff No: {{ $staff->staff_no ?? '—' }}
                    &middot;
                    {{ $staff->position ?? 'Staff Member' }}
                </p>
            </div>

            <span class="inline-flex px-3 py-2 rounded-xl bg-blue-50 text-blue-700 text-sm font-medium">
                Full Attendance History
            </span>
        </div>
    </div>

    {{-- Date filters --}}
    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm p-5">
        <h2 class="font-semibold text-gray-800 mb-4">Filter Attendance Period</h2>

        <form method="GET"
              action="{{ route('attendance.staff-history', $staff) }}"
              class="grid grid-cols-1 sm:grid-cols-3 gap-4">

            <div>
                <label for="start_date" class="block text-sm text-gray-600 mb-1">
                    From date
                </label>
                <input type="date" id="start_date" name="start_date"
                       value="{{ $startDate }}"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>

            <div>
                <label for="end_date" class="block text-sm text-gray-600 mb-1">
                    To date
                </label>
                <input type="date" id="end_date" name="end_date"
                       value="{{ $endDate }}"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="bg-blue-600 text-white rounded-xl px-5 py-2 text-sm hover:bg-blue-700">
                    Apply Filters
                </button>

                <a href="{{ route('attendance.staff-history', $staff) }}"
                   class="border border-gray-200 rounded-xl px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                    Clear
                </a>
            </div>
        </form>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="bg-white rounded-2xl border border-blue-50 shadow-sm p-5">
            <p class="text-sm text-gray-500">Recorded Scans</p>
            <p class="text-3xl font-bold text-blue-700 mt-2">
                {{ $totalScans }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-50 shadow-sm p-5">
            <p class="text-sm text-gray-500">Days with Attendance Scans</p>
            <p class="text-3xl font-bold text-green-700 mt-2">
                {{ $daysWithScans }}
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-blue-50 shadow-sm p-5">
            <p class="text-sm text-gray-500">Late Scan Records</p>
            <p class="text-3xl font-bold text-orange-600 mt-2">
                {{ $lateScans }}
            </p>
        </div>

    </div>

    {{-- Attendance records --}}
    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Attendance Records</h2>
            <p class="text-sm text-gray-500 mt-1">
                Scan history for the selected period.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Device</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @forelse ($records as $record)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($record->scan_date)->format('d M Y') }}
                            </td>

                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($record->scan_time)->format('H:i:s') }}
                            </td>

                            <td class="px-4 py-3">
                                <span @class([
                                    'px-3 py-1 rounded-full text-xs font-medium',
                                    'bg-blue-100 text-blue-700' => $record->scan_type === 'in',
                                    'bg-purple-100 text-purple-700' => $record->scan_type === 'out',
                                ])>
                                    {{ strtoupper($record->scan_type ?? '—') }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-gray-500">
                                {{ $record->device->device_name ?? $record->device->name ?? '—' }}
                            </td>

                            <td class="px-4 py-3">
                                <span @class([
                                    'px-3 py-1 rounded-full text-xs font-medium',
                                    'bg-green-100 text-green-700' => $record->status === 'on_time',
                                    'bg-yellow-100 text-yellow-700' => $record->status === 'late',
                                    'bg-red-100 text-red-700' => $record->status === 'absent',
                                    'bg-orange-100 text-orange-700' => $record->status === 'early_leave',
                                ])>
                                    {{ ucwords(str_replace('_', ' ', $record->status ?? 'unknown')) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                No attendance records found for this period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $records->links() }}
        </div>
    </div>

</div>
@endsection
