
@forelse ($records as $record)
    <tr data-record-id="{{ $record->id }}"
        class="hover:bg-gray-50 transition-colors">

        @if ($canViewAll)
            <td class="px-4 py-3 font-medium text-gray-800">
                {{ $record->staff->first_name ?? '—' }}
                {{ $record->staff->last_name ?? '' }}
            </td>
        @endif

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
    <tr>
        <td colspan="{{ $canViewAll ? ($isSystemAdmin ? 7 : 6) : 5 }}"
            class="px-4 py-8 text-center text-gray-400">
            No attendance records found.
        </td>
    </tr>
@endforelse
