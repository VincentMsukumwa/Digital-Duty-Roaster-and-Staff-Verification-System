@extends('layouts.app')

@php
    $role = auth()->user()->role->name ?? null;
    $canManage = in_array($role, ['System Administrator', 'Supervisor / Manager']);
    $isWorker = $role === 'Healthcare Worker';
@endphp

@section('title', $isWorker ? 'My Roster' : 'Duty Rosters')

@section('content')

    <div class="flex items-center justify-between mb-5">
        <form method="GET" action="{{ route('rosters.index') }}" class="flex items-center gap-3">
            @unless ($isWorker)
                <select name="department_id" onchange="this.form.submit()"
                        class="border border-gray-200 rounded-xl px-4 py-2 text-sm">
                    <option value="">All Departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            @endunless

            <select name="status" onchange="this.form.submit()"
                    class="border border-gray-200 rounded-xl px-4 py-2 text-sm">
                <option value="">All Statuses</option>
                @foreach (['draft', 'published', 'archived'] as $option)
                    <option value="{{ $option }}" @selected(request('status') === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </form>

@if ($canManage)
    <div class="flex items-center gap-3">
        <a href="{{ route('rosters.create') }}"
           class="bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-blue-700 transition">
            + Single Roster
        </a>

        <a href="{{ route('rosters.bulk.create') }}"
           class="bg-green-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-green-700 transition">
            + Bulk Rosters
        </a>
    </div>
@endif

    </div>

    @if (session('status'))
        <div class="bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($noStaffLink ?? false)
        <div class="bg-yellow-50 border border-yellow-100 text-yellow-800 px-4 py-3 rounded-xl mb-4 text-sm">
            Your account isn't linked to a staff profile yet, so there's no schedule to show.
            Ask your System Administrator to link your login to your staff record.
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm shadow-blue-100/50 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Staff</th>
                    <th class="px-4 py-3">Department</th>
                    <th class="px-4 py-3">Shift</th>
                    <th class="px-4 py-3">Work Days</th>
                    <th class="px-4 py-3">Effective</th>
                    <th class="px-4 py-3">Status</th>
                    @if ($canManage)
                        <th class="px-4 py-3 text-right">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rosters as $roster)
                    <tr class="hover:bg-blue-50/40">
                        <td class="px-4 py-3 font-medium text-gray-800">
    @if ($canManage)
        <a href="{{ route('attendance.staff-history', $roster->staff->id) }}"
           class="text-blue-600 hover:text-blue-800 hover:underline">
            {{ $roster->staff->first_name }} {{ $roster->staff->last_name }}
        </a>
    @else
        {{ $roster->staff->first_name }} {{ $roster->staff->last_name }}
    @endif
</td>
                        <td class="px-4 py-3">{{ $roster->department->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            {{ \Carbon\Carbon::parse($roster->shift_start)->format('H:i') }}
                            –
                            {{ \Carbon\Carbon::parse($roster->shift_end)->format('H:i') }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $roster->work_days ?: '—' }}</td>
                        <td class="px-4 py-3">
                            {{ $roster->effective_date->format('d M Y') }}
                            @if ($roster->end_date)
                                → {{ $roster->end_date->format('d M Y') }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span @class([
                                'px-3 py-1 rounded-full text-xs font-medium',
                                'bg-yellow-100 text-yellow-700' => $roster->status === 'draft',
                                'bg-green-100 text-green-700' => $roster->status === 'published',
                                'bg-gray-100 text-gray-600' => $roster->status === 'archived',
                            ])>
                                {{ ucfirst($roster->status) }}
                            </span>
                        </td>
                        @if ($canManage)
                            <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                                <a href="{{ route('attendance.staff-history', $roster->staff->id) }}"
                                class="text-indigo-600 hover:underline">
                             View Attendance
                              </a>  
                                <a href="{{ route('rosters.edit', $roster) }}" class="text-blue-600 hover:underline">Edit</a>

                                @if ($roster->status === 'draft')
                                    <form method="POST" action="{{ route('rosters.publish', $roster) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:underline">Publish</button>
                                    </form>
                                @endif

                                @if ($roster->status === 'published')
                                    <form method="POST" action="{{ route('rosters.archive', $roster) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-gray-600 hover:underline">Archive</button>
                                    </form>
                                @endif

                                @if ($roster->status !== 'published')
                                    <form method="POST" action="{{ route('rosters.destroy', $roster) }}" class="inline"
                                          onsubmit="return confirm('Delete this roster?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canManage ? 7 : 6 }}" class="px-4 py-8 text-center text-gray-400">
                            No rosters found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rosters->links() }}
    </div>

@endsection
