@extends('layouts.app')

@section('title', ($canViewAll ?? false) ? 'Attendance Records' : 'My Attendance')

@section('content')

    @if ($canViewAll)
        <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-3 mb-5">
            <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()"
                   class="border border-gray-200 rounded-xl px-4 py-2 text-sm">

            <select name="status" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl px-4 py-2 text-sm">
                <option value="">All Statuses</option>
                @foreach (['on_time', 'late', 'absent', 'early_leave'] as $option)
                    <option value="{{ $option }}" @selected(request('status') === $option)>
                        {{ ucwords(str_replace('_', ' ', $option)) }}
                    </option>
                @endforeach
            </select>

            @if (request('date') || request('status'))
                <a href="{{ route('attendance.index') }}" class="text-sm text-gray-500 hover:underline">Clear filters</a>
            @endif
        </form>
    @endif

    @if (session('status'))
        <div class="bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($noStaffLink ?? false)
        <div class="bg-yellow-100 text-yellow-800 px-4 py-3 rounded mb-4 text-sm">
            Your account isn't linked to a staff profile yet, so there's no attendance history to show.
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm shadow-blue-100/50 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    @if ($canViewAll)
                        <th class="px-4 py-3">Staff</th>
                    @endif
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Time</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Device</th>
                    <th class="px-4 py-3">Status</th>
                    @if ($isSystemAdmin)
                        <th class="px-4 py-3 text-right">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($records as $record)
                    <tr class="hover:bg-gray-50">
                        @if ($canViewAll)
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $record->staff->first_name ?? '—' }} {{ $record->staff->last_name ?? '' }}
                            </td>
                        @endif
                        <td class="px-4 py-3">{{ $record->scan_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($record->scan_time)->format('H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'px-3 py-1 rounded-full text-xs font-medium',
                                'bg-blue-100 text-blue-700' => $record->scan_type === 'in',
                                'bg-purple-100 text-purple-700' => $record->scan_type === 'out',
                            ])>
                                {{ strtoupper($record->scan_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $record->device->device_name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'px-3 py-1 rounded-full text-xs font-medium',
                                'bg-green-100 text-green-700' => $record->status === 'on_time',
                                'bg-yellow-100 text-yellow-700' => $record->status === 'late',
                                'bg-red-100 text-red-700' => $record->status === 'absent',
                                'bg-orange-100 text-orange-700' => $record->status === 'early_leave',
                            ])>
                                {{ ucwords(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </td>
                        @if ($isSystemAdmin)
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('attendance.destroy', $record) }}" class="inline"
                                      onsubmit="return confirm('Delete this attendance record? This is a logged administrative override (NFR-08) and cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canViewAll ? ($isSystemAdmin ? 7 : 6) : 5 }}" class="px-4 py-8 text-center text-gray-400">
                            No attendance records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $records->links() }}
    </div>

@endsection
