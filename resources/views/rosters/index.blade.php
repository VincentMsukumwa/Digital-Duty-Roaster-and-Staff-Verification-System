
@extends('layouts.app')

@php
    $role = auth()->user()->role->name ?? null;

    $canManage = in_array($role, [
        'System Administrator',
        'Supervisor / Manager'
    ]);

    $isWorker = $role === 'Healthcare Worker';

    // Summary counts for the currently displayed page.
    $publishedCount = $rosters->getCollection()
        ->where('status', 'published')
        ->count();

    $draftCount = $rosters->getCollection()
        ->where('status', 'draft')
        ->count();
@endphp

@section('title', $isWorker ? 'My Duty Roster' : 'Duty Rosters')

@section('content')

<div class="space-y-6">

    {{-- Page heading and actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <h2 class="text-2xl font-bold text-gray-900">
                {{ $isWorker ? 'My Duty Roster' : 'Duty Rosters' }}
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Manage and monitor staff duty schedules at QECH.
            </p>
        </div>

        @if ($canManage)
            <div class="flex flex-wrap items-center gap-2">

                <a href="{{ route('rosters.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5
                          bg-indigo-600 hover:bg-indigo-700
                          text-white text-sm font-semibold rounded-xl shadow-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Add Duty Roster
                </a>

                <a href="{{ route('rosters.bulk.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5
                          bg-emerald-600 hover:bg-emerald-700
                          text-white text-sm font-semibold rounded-xl shadow-sm">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Bulk Create
                </a>

            </div>
        @endif

    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="qech-card p-5">
            <p class="text-sm text-gray-500">Total displayed rosters</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">
                {{ $rosters->total() }}
            </p>
        </div>

        <div class="qech-card p-5">
            <p class="text-sm text-gray-500">Published on this page</p>
            <p class="text-2xl font-bold text-green-600 mt-2">
                {{ $publishedCount }}
            </p>
        </div>

        <div class="qech-card p-5">
            <p class="text-sm text-gray-500">Drafts on this page</p>
            <p class="text-2xl font-bold text-amber-600 mt-2">
                {{ $draftCount }}
            </p>
        </div>

    </div>

    {{-- Filters --}}
    @if ($canManage)
        <div class="qech-card p-5">

            <form method="GET"
                  action="{{ route('rosters.index') }}"
                  class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Department
                    </label>

                    <select name="department_id"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">

                        <option value="">All Departments</option>

                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}"
                                @selected(request('department_id') == $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Roster Status
                    </label>

                    <select name="status"
                            class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm">

                        <option value="">All Statuses</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="published" @selected(request('status') === 'published')>Published</option>
                        <option value="archived" @selected(request('status') === 'archived')>Archived</option>

                    </select>
                </div>

                <div class="flex items-end gap-2">

                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-gray-900
                                   hover:bg-gray-700 text-white
                                   text-sm font-medium rounded-xl">
                        Apply Filters
                    </button>

                    <a href="{{ route('rosters.index') }}"
                       class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200
                              text-gray-700 text-sm rounded-xl">
                        Reset
                    </a>

                </div>

            </form>

        </div>
    @endif

    {{-- Roster table --}}
    <div class="qech-card overflow-hidden">

        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900">Roster Records</h3>
                <p class="text-xs text-gray-500 mt-1">
                    Current duty schedules and their publication status.
                </p>
            </div>

            <span class="text-xs text-gray-500">
                {{ $rosters->total() }} records
            </span>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left min-w-[1000px]">

                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-4">Staff Member</th>
                        <th class="px-5 py-4">Department</th>
                        <th class="px-5 py-4">Shift</th>
                        <th class="px-5 py-4">Work Days</th>
                        <th class="px-5 py-4">Effective Period</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($rosters as $roster)

                        <tr class="hover:bg-gray-50">

                            {{-- Staff --}}
                            <td class="px-5 py-4">
                                @if ($roster->staff)
                                    <p class="font-semibold text-gray-900">
                                        {{ $roster->staff->first_name }}
                                        {{ $roster->staff->last_name }}
                                    </p>

                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $roster->staff->staff_no }}
                                    </p>
                                @else
                                    <span class="text-gray-400 italic">
                                        Staff unavailable
                                    </span>
                                @endif
                            </td>

                            {{-- Department --}}
                            <td class="px-5 py-4 text-gray-600">
                                {{ $roster->department->name ?? 'N/A' }}
                            </td>

                            {{-- Shift --}}
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-800">
                                    {{ \Carbon\Carbon::parse($roster->shift_start)->format('H:i') }}
                                    –
                                    {{ \Carbon\Carbon::parse($roster->shift_end)->format('H:i') }}
                                </p>
                            </td>

                            {{-- Work days --}}
                            <td class="px-5 py-4 text-gray-600">
                                {{ str_replace(',', ', ', $roster->work_days ?? '') ?: 'N/A' }}
                            </td>

                            {{-- Dates --}}
                            <td class="px-5 py-4 text-gray-600">
                                <p>
                                    {{ \Carbon\Carbon::parse($roster->effective_date)->format('d M Y') }}
                                </p>

                                <p class="text-xs text-gray-400 mt-1">
                                    to
                                    {{ $roster->end_date
                                        ? \Carbon\Carbon::parse($roster->end_date)->format('d M Y')
                                        : 'Until further notice' }}
                                </p>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @php
                                    $statusClasses = [
                                        'draft' => 'bg-amber-100 text-amber-700',
                                        'published' => 'bg-green-100 text-green-700',
                                        'archived' => 'bg-gray-200 text-gray-700',
                                    ];

                                    $statusClass = $statusClasses[$roster->status]
                                        ?? 'bg-gray-100 text-gray-600';
                                @endphp

                                <span class="inline-flex px-3 py-1 rounded-full
                                             text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst($roster->status) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    @if ($roster->staff)
                                        <a href="{{ route('attendance.index', ['staff_id' => $roster->staff_id]) }}"
                                           class="px-3 py-2 rounded-lg bg-blue-50
                                                  hover:bg-blue-100 text-blue-700
                                                  text-xs font-medium">
                                            Attendance
                                        </a>
                                    @endif

                                    @if ($canManage)

                                        <a href="{{ route('rosters.edit', $roster) }}"
                                           class="px-3 py-2 rounded-lg bg-indigo-50
                                                  hover:bg-indigo-100 text-indigo-700
                                                  text-xs font-medium">
                                            Edit
                                        </a>

                                        @if ($roster->status === 'draft')

                                            <form method="POST"
                                                  action="{{ route('rosters.publish', $roster) }}"
                                                  onsubmit="return confirm('Publish this roster?')">
                                                @csrf

                                                <button type="submit"
                                                        class="px-3 py-2 rounded-lg bg-green-50
                                                               hover:bg-green-100 text-green-700
                                                               text-xs font-medium">
                                                    Publish
                                                </button>
                                            </form>

                                        @elseif ($roster->status === 'published')

                                            <form method="POST"
                                                  action="{{ route('rosters.archive', $roster) }}"
                                                  onsubmit="return confirm('Archive this roster?')">
                                                @csrf

                                                <button type="submit"
                                                        class="px-3 py-2 rounded-lg bg-amber-50
                                                               hover:bg-amber-100 text-amber-700
                                                               text-xs font-medium">
                                                    Archive
                                                </button>
                                            </form>

                                        @endif

                                        @if (in_array($roster->status, ['draft', 'archived']))

                                            <form method="POST"
                                                  action="{{ route('rosters.destroy', $roster) }}"
                                                  onsubmit="return confirm('Delete this roster?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="px-3 py-2 rounded-lg bg-red-50
                                                               hover:bg-red-100 text-red-700
                                                               text-xs font-medium">
                                                    Delete
                                                </button>
                                            </form>

                                        @endif

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">

                                <div class="text-gray-400 mb-3 flex justify-center">
                                    <i data-lucide="clipboard-list" class="w-10 h-10"></i>
                                </div>

                                <p class="font-semibold text-gray-700">
                                    No duty rosters found.
                                </p>

                                <p class="text-sm text-gray-500 mt-1">
                                    Create a roster to get started.
                                </p>

                                @if ($canManage)
                                    <a href="{{ route('rosters.create') }}"
                                       class="inline-flex items-center gap-2 mt-4 px-4 py-2
                                              bg-indigo-600 hover:bg-indigo-700
                                              text-white rounded-lg text-sm">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                        Add Duty Roster
                                    </a>
                                @endif

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        @if ($rosters->hasPages())
            <div class="p-5 border-t border-gray-100">
                {{ $rosters->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
