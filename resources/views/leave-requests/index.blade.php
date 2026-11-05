@extends('layouts.app')

@section('title', $canManage ? 'Leave Requests' : 'My Leave Requests')

@section('content')

    <div class="flex items-center justify-between mb-5">
        @if ($canManage)
            <form method="GET" action="{{ route('leave-requests.index') }}">
                <select name="status" onchange="this.form.submit()"
                        class="border border-gray-200 rounded-xl px-4 py-2 text-sm">
                    <option value="">All Statuses</option>
                    @foreach (['pending', 'approved', 'rejected'] as $option)
                        <option value="{{ $option }}" @selected(request('status') === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </form>
        @else
            <div></div>
        @endif

        <a href="{{ route('leave-requests.create') }}"
           class="bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-blue-700 transition">
            + Request Leave
        </a>
    </div>

    @if (session('status'))
        <div class="bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm shadow-blue-100/50 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    @if ($canManage)
                        <th class="px-4 py-3">Staff</th>
                    @endif
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Dates</th>
                    <th class="px-4 py-3">Reason</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Decided By</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($leaveRequests as $leave)
                    <tr class="hover:bg-gray-50">
                        @if ($canManage)
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $leave->staff->first_name ?? '—' }} {{ $leave->staff->last_name ?? '' }}
                            </td>
                        @endif
                        <td class="px-4 py-3">{{ $leave->leave_type }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $leave->reason ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'px-3 py-1 rounded-full text-xs font-medium',
                                'bg-yellow-100 text-yellow-700' => $leave->status === 'pending',
                                'bg-green-100 text-green-700' => $leave->status === 'approved',
                                'bg-red-100 text-red-700' => $leave->status === 'rejected',
                            ])>
                                {{ ucfirst($leave->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $leave->approver->username ?? '—' }}</td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            @if ($canManage && $leave->status === 'pending')
                                <form method="POST" action="{{ route('leave-requests.approve', $leave) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:underline">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('leave-requests.reject', $leave) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:underline">Reject</button>
                                </form>
                            @endif

                            @if ($canManage || $leave->status === 'pending')
                                <form method="POST" action="{{ route('leave-requests.destroy', $leave) }}" class="inline"
                                      onsubmit="return confirm('Remove this leave request?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-500 hover:underline">
                                        {{ $canManage ? 'Delete' : 'Cancel' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canManage ? 7 : 6 }}" class="px-4 py-8 text-center text-gray-400">
                            No leave requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $leaveRequests->links() }}
    </div>

@endsection
