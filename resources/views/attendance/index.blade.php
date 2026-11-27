
@extends('layouts.app')

@section('title', ($canViewAll ?? false) ? 'Attendance Records' : 'My Attendance')

@section('content')

    {{-- Live attendance status --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">

        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                Attendance Monitoring
            </h2>
            <p class="text-sm text-gray-500">
                Monitor staff attendance records and fingerprint scans.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span id="liveIndicator"
                  class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse">
            </span>

            <span id="liveStatus" class="text-sm font-medium text-green-700">
                Live Updates Active
            </span>

            <span class="text-xs text-gray-400">
                (3-second interval)
            </span>
        </div>

    </div>

    {{-- Filters --}}
    @if ($canViewAll)
        <form id="attendanceFilters"
              method="GET"
              action="{{ route('attendance.index') }}"
              class="flex flex-wrap items-center gap-3 mb-5">

            <input type="date"
                   name="date"
                   value="{{ request('date') }}"
                   onchange="this.form.submit()"
                   class="border border-gray-200 rounded-xl px-4 py-2 text-sm">

            <select name="status"
                    onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl px-4 py-2 text-sm">

                <option value="">All Statuses</option>

                @foreach (['on_time', 'late', 'absent', 'early_leave'] as $option)
                    <option value="{{ $option }}" @selected(request('status') === $option)>
                        {{ ucwords(str_replace('_', ' ', $option)) }}
                    </option>
                @endforeach

            </select>

            @if (request('date') || request('status'))
                <a href="{{ route('attendance.index') }}"
                   class="text-sm text-gray-500 hover:underline">
                    Clear filters
                </a>
            @endif

        </form>
    @endif

    {{-- Flash messages --}}
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

    {{-- Latest scan notification --}}
    <div id="newScanNotification"
         class="hidden bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-4 text-sm">
    </div>

    {{-- Attendance table --}}
    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm shadow-blue-100/50 overflow-hidden">

        <div class="overflow-x-auto">

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

                <tbody id="attendanceTableBody" class="divide-y divide-gray-100">

                    @forelse ($records as $record)

                        <tr class="hover:bg-gray-50"
                            data-record-id="{{ $record->id }}">

                            @if ($canViewAll)
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $record->staff->first_name ?? '—' }}
                                    {{ $record->staff->last_name ?? '' }}
                                </td>
                            @endif

                            <td class="px-4 py-3">
                                {{ $record->scan_date->format('d M Y') }}
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
                                    {{ strtoupper($record->scan_type) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-gray-500">
                                {{ $record->device->device_name ?? '—' }}
                            </td>

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

                                    <form method="POST"
                                          action="{{ route('attendance.destroy', $record) }}"
                                          class="inline"
                                          onsubmit="return confirm('Delete this attendance record? This is a logged administrative override (NFR-08) and cannot be undone.');">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="text-red-600 hover:underline">
                                            Delete
                                        </button>

                                    </form>

                                </td>
                            @endif

                        </tr>

                    @empty

                        <tr id="emptyAttendanceRow">
                            <td colspan="{{ $canViewAll ? ($isSystemAdmin ? 7 : 6) : 5 }}"
                                class="px-4 py-8 text-center text-gray-400">
                                No attendance records found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{-- Pagination --}}
    <div class="mt-4" id="attendancePagination">
        {{ $records->links() }}
    </div>

    {{-- Live update script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const tableBody = document.getElementById('attendanceTableBody');
            const liveStatus = document.getElementById('liveStatus');
            const liveIndicator = document.getElementById('liveIndicator');
            const notification = document.getElementById('newScanNotification');

            const canViewAll = @json($canViewAll);
            const isSystemAdmin = @json($isSystemAdmin);

            const dateFilter = @json(request('date'));
            const statusFilter = @json(request('status'));

            const refreshUrl = @json(route('attendance.index'));

            let knownRecordIds = new Set(
                Array.from(tableBody.querySelectorAll('[data-record-id]'))
                    .map(row => row.dataset.recordId)
            );

            let notificationTimeout = null;
            let requestInProgress = false;

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, function (char) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    }[char];
                });
            }

            function statusBadge(status) {
                const classes = {
                    on_time: 'bg-green-100 text-green-700',
                    late: 'bg-yellow-100 text-yellow-700',
                    absent: 'bg-red-100 text-red-700',
                    early_leave: 'bg-orange-100 text-orange-700'
                };

                return `<span class="px-3 py-1 rounded-full text-xs font-medium ${classes[status] ?? 'bg-gray-100 text-gray-700'}">
                    ${escapeHtml(status.replaceAll('_', ' ').replace(/\b\w/g, c => c.toUpperCase()))}
                </span>`;
            }

            function typeBadge(type) {
                const color = type === 'in'
                    ? 'bg-blue-100 text-blue-700'
                    : 'bg-purple-100 text-purple-700';

                return `<span class="px-3 py-1 rounded-full text-xs font-medium ${color}">
                    ${escapeHtml(type.toUpperCase())}
                </span>`;
            }

            function buildRow(record, isNew = false) {

                const row = document.createElement('tr');

                row.dataset.recordId = record.id;

                row.className = isNew
                    ? 'bg-green-50 hover:bg-green-100 transition-colors'
                    : 'hover:bg-gray-50';

                let html = '';

                if (canViewAll) {
                    html += `
                        <td class="px-4 py-3 font-medium text-gray-800">
                            ${escapeHtml(record.staff?.first_name ?? '—')}
                            ${escapeHtml(record.staff?.last_name ?? '')}
                        </td>
                    `;
                }

                html += `
                    <td class="px-4 py-3">${escapeHtml(record.scan_date_formatted)}</td>
                    <td class="px-4 py-3">${escapeHtml(record.scan_time_formatted)}</td>
                    <td class="px-4 py-3">${typeBadge(record.scan_type)}</td>
                    <td class="px-4 py-3 text-gray-500">
                        ${escapeHtml(record.device?.device_name ?? '—')}
                    </td>
                    <td class="px-4 py-3">${statusBadge(record.status)}</td>
                `;

                if (isSystemAdmin) {
                    html += `
                        <td class="px-4 py-3 text-right">
                            <form method="POST"
                                  action="${escapeHtml(record.delete_url)}"
                                  class="inline"
                                  onsubmit="return confirm('Delete this attendance record? This is a logged administrative override (NFR-08) and cannot be undone.');">
                                <input type="hidden" name="_token" value="${escapeHtml(record.csrf_token)}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            </form>
                        </td>
                    `;
                }

                row.innerHTML = html;

                return row;
            }

            async function refreshAttendance() {

                if (requestInProgress || document.hidden) {
                    return;
                }

                requestInProgress = true;

                try {

                    const params = new URLSearchParams();

                    if (dateFilter) params.set('date', dateFilter);
                    if (statusFilter) params.set('status', statusFilter);

                    params.set('live', '1');

                    const response = await fetch(
                        `${refreshUrl}?${params.toString()}`,
                        {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            cache: 'no-store'
                        }
                    );

                    if (!response.ok) {
                        throw new Error('Unable to retrieve attendance updates.');
                    }

                    const data = await response.json();

                    const records = data.records ?? [];

                    const newRecords = records.filter(record =>
                        !knownRecordIds.has(String(record.id))
                    );

                    if (newRecords.length > 0) {

                        const emptyRow = document.getElementById('emptyAttendanceRow');

                        if (emptyRow) {
                            emptyRow.remove();
                        }

                        newRecords.reverse().forEach(record => {

                            const row = buildRow(record, true);

                            tableBody.insertBefore(row, tableBody.firstChild);

                            knownRecordIds.add(String(record.id));

                            if (row.animate) {
                                row.animate(
                                    [
                                        { opacity: 0, transform: 'translateY(-8px)' },
                                        { opacity: 1, transform: 'translateY(0)' }
                                    ],
                                    { duration: 350, easing: 'ease-out' }
                                );
                            }
                        });

                        const latest = newRecords[0];

                        notification.textContent =
                            `New attendance recorded: ${latest.staff?.first_name ?? 'Staff'} ${latest.staff?.last_name ?? ''} — ${latest.scan_type.toUpperCase()} at ${latest.scan_time_formatted}`;

                        notification.classList.remove('hidden');

                        clearTimeout(notificationTimeout);

                        notificationTimeout = setTimeout(() => {
                            notification.classList.add('hidden');
                        }, 6000);

                    }

                    liveStatus.textContent = 'Live Updates Active';
                    liveStatus.className = 'text-sm font-medium text-green-700';

                    liveIndicator.className =
                        'w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse';

                } catch (error) {

                    liveStatus.textContent = 'Connection interrupted — retrying';
                    liveStatus.className = 'text-sm font-medium text-red-600';

                    liveIndicator.className =
                        'w-2.5 h-2.5 rounded-full bg-red-500';

                    console.error('Attendance live update:', error);

                } finally {
                    requestInProgress = false;
                }
            }

            // Check for new attendance every 3 seconds.
            setInterval(refreshAttendance, 3000);

        });
    </script>

@endsection
