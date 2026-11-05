```blade
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            @if ($staff)
                <p class="text-sm font-medium text-blue-600 mb-1">STAFF WORKSPACE</p>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">
                    Welcome, {{ $staff->first_name }} {{ $staff->last_name }}
                </h1>
                <p class="text-sm text-gray-500 mt-2">
                    Here is your duty and attendance overview.
                </p>
            @elseif ($role === 'System Administrator')
                <p class="text-sm font-medium text-blue-600 mb-1">ADMINISTRATION</p>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">
                    Welcome back, Administrator
                </h1>
                <p class="text-sm text-gray-500 mt-2">
                    Manage QECH staff, duty rosters and attendance.
                </p>
            @else
                <p class="text-sm font-medium text-blue-600 mb-1">QECH WORKSPACE</p>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">
                    Dashboard
                </h1>
                <p class="text-sm text-gray-500 mt-2">
                    Your account overview and available services.
                </p>
            @endif
        </div>

        <div class="inline-flex items-center gap-3 self-start sm:self-auto rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500">Today's date</p>
                <p class="text-sm font-semibold text-gray-800">
                    {{ now()->format('d M Y') }}
                </p>
            </div>
        </div>
    </div>

    {{-- STAFF DASHBOARD --}}
    @if ($staff)

        {{-- PROFILE SUMMARY --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">

            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-sm text-gray-500">Staff profile</p>
                        <h2 class="mt-2 text-lg font-bold text-gray-900 break-words">
                            {{ $staff->first_name }} {{ $staff->last_name }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Staff No: {{ $staff->staff_no }}
                        </p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-lg font-bold text-blue-700">
                        {{ strtoupper(substr($staff->first_name, 0, 1)) }}
                    </div>
                </div>
                <div class="mt-5 border-t border-gray-100 pt-4">
                    <span class="inline-flex rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                        {{ $staff->position ?? 'Staff' }}
                    </span>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Department</p>
                        <h2 class="mt-2 text-lg font-bold text-gray-900 break-words">
                            {{ $staff->department->name ?? 'Not assigned' }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $role ?? 'Healthcare Worker' }}
                        </p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4"/>
                            <path stroke-linecap="round" d="M9 10h1m4 0h1m-6 4h1m4 0h1"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-5 border-t border-gray-100 pt-4">
                    <p class="text-xs text-gray-500">QECH staff member</p>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Attendance method</p>
                        <h2 class="mt-2 text-lg font-bold text-gray-900">Fingerprint scan</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Electronic attendance verification
                        </p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 11a2 2 0 012 2v4"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 13a4 4 0 018 0v4M6 13a6 6 0 0112 0v2"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M10 17v-4a2 2 0 014 0v4"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-5 border-t border-gray-100 pt-4">
                    <a href="{{ route('attendance.index') }}"
                       class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                        View attendance history →
                    </a>
                </div>
            </div>
        </div>

        {{-- TODAY'S DUTY AND ATTENDANCE --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

            {{-- DUTY CARD --}}
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-5 sm:px-6">
                    <div>
                        <h2 class="font-bold text-gray-900">Today's duty</h2>
                        <p class="mt-1 text-sm text-gray-500">Your scheduled shift</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8">
                            <rect x="4" y="5" width="16" height="16" rx="2"/>
                            <path stroke-linecap="round" d="M8 3v4m8-4v4M4 10h16"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m8 15 2 2 5-4"/>
                        </svg>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    @if ($todayRoster)
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                Department
                            </p>
                            <p class="mt-2 font-semibold text-gray-900">
                                {{ $todayRoster->department->name ?? 'Not specified' }}
                            </p>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-4">
                            <div class="rounded-xl border border-gray-200 p-4">
                                <p class="text-xs text-gray-500">Shift starts</p>
                                <p class="mt-2 text-xl font-bold text-gray-900">
                                    {{ \Carbon\Carbon::parse($todayRoster->shift_start)->format('H:i') }}
                                </p>
                            </div>
                            <div class="rounded-xl border border-gray-200 p-4">
                                <p class="text-xs text-gray-500">Shift ends</p>
                                <p class="mt-2 text-xl font-bold text-gray-900">
                                    {{ \Carbon\Carbon::parse($todayRoster->shift_end)->format('H:i') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4">
                            <span class="text-sm text-gray-500">Roster status</span>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                                {{ $todayRoster->status === 'published'
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-amber-50 text-amber-700' }}">
                                {{ ucfirst($todayRoster->status) }}
                            </span>
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="1.8">
                                    <rect x="4" y="5" width="16" height="16" rx="2"/>
                                    <path stroke-linecap="round" d="M8 3v4m8-4v4M4 10h16"/>
                                </svg>
                            </div>
                            <h3 class="mt-4 font-semibold text-gray-800">No duty assigned today</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                There is no duty roster assigned to you for today.
                            </p>
                            <a href="{{ route('rosters.index') }}"
                               class="mt-4 inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                                View my rosters
                            </a>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ATTENDANCE CARD --}}
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-5 sm:px-6">
                    <div>
                        <h2 class="font-bold text-gray-900">Today's attendance</h2>
                        <p class="mt-1 text-sm text-gray-500">Your recorded fingerprint scans</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m8.5 12 2.3 2.3 4.7-4.7"/>
                        </svg>
                    </div>
                </div>

                <div class="p-5 sm:p-6">
                    @if ($todayAttendance->count())
                        <div class="space-y-0">
                            @foreach ($todayAttendance as $record)
                                <div class="flex items-center justify-between gap-4 border-b border-gray-100 py-4 first:pt-0 last:border-0 last:pb-0">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                                            {{ strtolower($record->scan_type) === 'in'
                                                ? 'bg-emerald-50 text-emerald-600'
                                                : 'bg-blue-50 text-blue-600' }}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor" stroke-width="1.8">
                                                <circle cx="12" cy="12" r="9"/>
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 7v5l3 2"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold uppercase text-gray-900">
                                                {{ $record->scan_type }}
                                            </p>
                                            <p class="mt-1 truncate text-xs text-gray-500">
                                                {{ $record->device->device_name ?? 'Attendance Device' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ \Carbon\Carbon::parse($record->scan_time)->format('H:i:s') }}
                                        </p>
                                        <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-medium
                                            {{ $record->status === 'on_time'
                                                ? 'bg-emerald-50 text-emerald-700'
                                                : 'bg-amber-50 text-amber-700' }}">
                                            {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 7v5l3 2"/>
                                </svg>
                            </div>
                            <h3 class="mt-4 font-semibold text-gray-800">No attendance recorded</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Scan your fingerprint at the attendance device.
                            </p>
                        </div>
                    @endif

                    <div class="mt-5 border-t border-gray-100 pt-4">
                        <a href="{{ route('attendance.index') }}"
                           class="text-sm font-semibold text-blue-600 hover:text-blue-800">
                            View full attendance history →
                        </a>
                    </div>
                </div>
            </section>
        </div>

        {{-- QUICK ACCESS --}}
        <section>
            <div class="mb-4">
                <h2 class="text-lg font-bold text-gray-900">Quick access</h2>
                <p class="mt-1 text-sm text-gray-500">Shortcuts to your staff services.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <a href="{{ route('rosters.index') }}"
                   class="group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path stroke-linecap="round" d="M8 3v4m8-4v4M3 10h18"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-gray-900">My roster</h3>
                        <p class="mt-1 text-sm text-gray-500">View your duty schedule</p>
                    </div>
                    <span class="text-gray-400 transition group-hover:translate-x-1 group-hover:text-blue-600">→</span>
                </a>

                <a href="{{ route('attendance.index') }}"
                   class="group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 11a2 2 0 012 2v4M8 13a4 4 0 018 0v4M6 13a6 6 0 0112 0v2"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-gray-900">My attendance</h3>
                        <p class="mt-1 text-sm text-gray-500">Review your attendance history</p>
                    </div>
                    <span class="text-gray-400 transition group-hover:translate-x-1 group-hover:text-emerald-600">→</span>
                </a>

                <a href="{{ route('leave-requests.index') }}"
                   class="group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-md">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M7 3h7l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2zM14 3v5h5"/>
                            <path stroke-linecap="round" d="M9 13h6m-6 4h4"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-gray-900">Leave requests</h3>
                        <p class="mt-1 text-sm text-gray-500">Submit and track leave</p>
                    </div>
                    <span class="text-gray-400 transition group-hover:translate-x-1 group-hover:text-violet-600">→</span>
                </a>
            </div>
        </section>

    
    
{{-- SYSTEM ADMINISTRATOR DASHBOARD --}}
@elseif ($role === 'System Administrator')

    @php
        $adminCards = [
            [
                'title' => 'Staff Management',
                'description' => 'Register staff and manage profiles and fingerprint enrolment.',
                'route' => 'staff.index',
                'label' => 'Manage staff',
                'color' => 'blue',
                'icon' => 'users',
            ],
            [
                'title' => 'Attendance Records',
                'description' => 'Review fingerprint scans and staff attendance statuses.',
                'route' => 'attendance.index',
                'label' => 'View attendance',
                'color' => 'emerald',
                'icon' => 'check',
            ],
            [
                'title' => 'Duty Rosters',
                'description' => 'Manage staff schedules and published duty assignments.',
                'route' => 'rosters.index',
                'label' => 'Manage rosters',
                'color' => 'violet',
                'icon' => 'calendar',
            ],
            [
                'title' => 'Reports & Analytics',
                'description' => 'Access attendance and duty roster reports.',
                'route' => 'reports.index',
                'label' => 'View reports',
                'color' => 'orange',
                'icon' => 'chart',
            ],
            [
                'title' => 'Departments',
                'description' => 'Organise hospital departments and staff allocation.',
                'route' => 'departments.index',
                'label' => 'Manage departments',
                'color' => 'teal',
                'icon' => 'building',
            ],
            [
                'title' => 'User Accounts',
                'description' => 'Manage system users and their assigned roles.',
                'route' => 'users.index',
                'label' => 'Manage users',
                'color' => 'indigo',
                'icon' => 'user',
            ],
        ];

        $adminColors = [
            'blue' => [
                'iconBg' => 'bg-blue-50',
                'iconText' => 'text-blue-600',
                'accent' => 'bg-blue-500',
                'link' => 'text-blue-700',
                'hover' => 'group-hover:border-blue-200',
            ],
            'emerald' => [
                'iconBg' => 'bg-emerald-50',
                'iconText' => 'text-emerald-600',
                'accent' => 'bg-emerald-500',
                'link' => 'text-emerald-700',
                'hover' => 'group-hover:border-emerald-200',
            ],
            'violet' => [
                'iconBg' => 'bg-violet-50',
                'iconText' => 'text-violet-600',
                'accent' => 'bg-violet-500',
                'link' => 'text-violet-700',
                'hover' => 'group-hover:border-violet-200',
            ],
            'orange' => [
                'iconBg' => 'bg-orange-50',
                'iconText' => 'text-orange-600',
                'accent' => 'bg-orange-500',
                'link' => 'text-orange-700',
                'hover' => 'group-hover:border-orange-200',
            ],
            'teal' => [
                'iconBg' => 'bg-teal-50',
                'iconText' => 'text-teal-600',
                'accent' => 'bg-teal-500',
                'link' => 'text-teal-700',
                'hover' => 'group-hover:border-teal-200',
            ],
            'indigo' => [
                'iconBg' => 'bg-indigo-50',
                'iconText' => 'text-indigo-600',
                'accent' => 'bg-indigo-500',
                'link' => 'text-indigo-700',
                'hover' => 'group-hover:border-indigo-200',
            ],
        ];
    @endphp

    <section class="space-y-6">

        {{-- SECTION HEADING --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">
                    System overview
                </p>

                <h2 class="mt-1 text-xl font-bold tracking-tight text-gray-900">
                    Management Centre
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Quick access to QECH attendance and staff management.
                </p>
            </div>

            <span class="inline-flex w-fit items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-600">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Administrator workspace
            </span>
        </div>

        {{-- MANAGEMENT CARDS --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            @foreach ($adminCards as $card)
                <a href="{{ route($card['route']) }}"
                   class="group relative flex min-h-[174px] flex-col overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md {{ $adminColors[$card['color']]['hover'] }}">

                    <div class="absolute inset-x-0 top-0 h-1 {{ $adminColors[$card['color']]['accent'] }}"></div>

                    <div class="flex items-center justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $adminColors[$card['color']]['iconBg'] }} {{ $adminColors[$card['color']]['iconText'] }}">

                            @if ($card['icon'] === 'users')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="9" cy="8" r="4"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 21v-2a7 7 0 0114 0v2M19 8v6m-3-3h6"/>
                                </svg>
                            @elseif ($card['icon'] === 'check')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.5 2.8 8 7 10 4.2-2 7-5.5 7-10V6l-7-3Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/>
                                </svg>
                            @elseif ($card['icon'] === 'calendar')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path stroke-linecap="round" d="M8 3v4m8-4v4M3 10h18"/>
                                </svg>
                            @elseif ($card['icon'] === 'chart')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/>
                                    <path stroke-linecap="round" d="M8 17v-4m5 4V7m5 10v-8"/>
                                </svg>
                            @elseif ($card['icon'] === 'building')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14"/>
                                    <path stroke-linecap="round" d="M9 10h1m4 0h1m-6 4h1m4 0h1"/>
                                </svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 21v-2a8 8 0 0116 0v2"/>
                                </svg>
                            @endif
                        </div>

                        <span class="text-lg text-gray-400 transition group-hover:translate-x-1 group-hover:text-gray-800">
                            ↗
                        </span>
                    </div>

                    <div class="mt-3 flex-1">
                        <h3 class="text-sm font-bold text-gray-900">
                            {{ $card['title'] }}
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-gray-500">
                            {{ $card['description'] }}
                        </p>
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3">
                        <span class="text-xs font-semibold {{ $adminColors[$card['color']]['link'] }}">
                            {{ $card['label'] }}
                        </span>
                        <span class="{{ $adminColors[$card['color']]['link'] }}">→</span>
                    </div>
                </a>
            @endforeach

        </div>

        {{-- TODAY'S ATTENDANCE ACTIVITY CHART --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">

            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">
                        Today's Attendance Activity
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Fingerprint scans recorded throughout the day.
                    </p>
                </div>

                <span class="w-fit rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                    {{ now()->format('d M Y') }}
                </span>
            </div>

            <div class="relative w-full" style="height: 320px;">
                <canvas id="attendanceChart"></canvas>
            </div>

            <div class="mt-4 flex items-center gap-2 text-xs text-gray-500">
                <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                Total fingerprint scans per hour
            </div>
        </div>

{{-- ATTENDANCE ACTIVITY TABLE --}}
<div id="attendanceActivityPanel"
     class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

    <div class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-900">
                Attendance Activity
            </h2>
            <p id="attendanceFilterLabel" class="mt-1 text-sm text-gray-500">
                Showing today's fingerprint scans
            </p>
        </div>

        <button type="button"
                id="resetAttendanceFilter"
                class="w-fit rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            Show all scans
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Staff member</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Staff No.</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Scan time</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Type</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Device</th>
                </tr>
            </thead>

            <tbody id="attendanceActivityBody" class="divide-y divide-gray-100 bg-white">
                @forelse ($attendanceActivity as $record)
                    @php
                        $staffMember = $record->staff;

                        $staffName = trim(
                            ($staffMember->first_name ?? '') . ' ' .
                            ($staffMember->last_name ?? '')
                        );

                        if ($staffName === '') {
                            $staffName = $staffMember->names
                                ?? 'Staff #' . $record->staff_id;
                        }

                        $scanDateTime = \Carbon\Carbon::parse(
                            $record->scan_date->format('Y-m-d') . ' ' . $record->scan_time
                        );

                        $status = strtolower((string) $record->status);
                    @endphp

                    <tr class="attendance-activity-row hover:bg-gray-50"
                        data-hour="{{ $scanDateTime->format('G') }}">

                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $staffName }}
                            </div>
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                            {{ $staffMember->staff_no ?? '—' }}
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                            {{ $scanDateTime->format('H:i:s') }}
                        </td>

                        <td class="whitespace-nowrap px-5 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ strtolower((string) $record->scan_type) === 'in'
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-blue-50 text-blue-700' }}">
                                {{ strtoupper($record->scan_type ?? '—') }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-5 py-4">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ in_array($status, ['on_time', 'present'])
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : (in_array($status, ['late', 'early_leave'])
                                        ? 'bg-amber-50 text-amber-700'
                                        : 'bg-gray-100 text-gray-700') }}">
                                {{ ucwords(str_replace('_', ' ', $record->status ?? 'Unknown')) }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">
                            {{ $record->device->name
                                ?? $record->device->device_name
                                ?? 'Device #' . $record->device_id }}
                        </td>
                    </tr>
                @empty
                    <tr id="attendanceEmptyRow">
                        <td colspan="6" class="px-5 py-10 text-center">
                            <p class="text-sm font-semibold text-gray-700">
                                No attendance scans recorded today
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                Records will appear here when staff scan their fingerprints.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-t border-gray-100 px-5 py-3">
        <p id="attendanceRowCount" class="text-xs text-gray-500">
            {{ $attendanceActivity->count() }} scan(s) recorded today
        </p>
    </div>
</div>


    </section>

    {{-- CHART.JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('attendanceChart');

            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            const attendanceData = @json($attendanceChart ?? []);

         
const attendanceChartInstance = new Chart(canvas, {
    type: 'line',

    data: {
        labels: attendanceData.map(item => item.hour),

        datasets: [{
            label: 'Fingerprint scans',
            data: attendanceData.map(item => item.total),
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37, 99, 235, 0.10)',
            borderWidth: 3,
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 7,
            pointBackgroundColor: '#2563eb'
        }]
    },

    options: {
        responsive: true,
        maintainAspectRatio: false,

        interaction: {
            intersect: false,
            mode: 'index'
        },

        // Filter the attendance table when an hour is clicked
        onClick: function (event, elements) {
            if (!elements.length) return;

            const selectedIndex = elements[0].index;
            const selectedHour = selectedIndex;
            const selectedLabel = attendanceData[selectedIndex].hour;

            const rows = document.querySelectorAll('.attendance-activity-row');
            let visibleCount = 0;

            rows.forEach(function (row) {
                const matches = Number(row.dataset.hour) === selectedHour;

                row.style.display = matches ? '' : 'none';

                if (matches) visibleCount++;
            });

            const filterLabel = document.getElementById('attendanceFilterLabel');
            const rowCount = document.getElementById('attendanceRowCount');

            if (filterLabel) {
                filterLabel.textContent =
                    'Showing scans recorded during ' + selectedLabel;
            }

            if (rowCount) {
                rowCount.textContent =
                    visibleCount + ' scan(s) recorded during ' + selectedLabel;
            }
        },

        plugins: {
            legend: {
                display: false
            },

            tooltip: {
                callbacks: {
                    label: function (context) {
                        return context.parsed.y + ' scan(s)';
                    }
                }
            }
        },

        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Hour of the day'
                },
                ticks: {
                    maxTicksLimit: 12,
                    maxRotation: 0
                },
                grid: {
                    display: false
                }
            },

            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                },
                title: {
                    display: true,
                    text: 'Number of scans'
                }
            }
        }
    }
});

// Reset the table to show all today's scans
const resetButton = document.getElementById('resetAttendanceFilter');

if (resetButton) {
    resetButton.addEventListener('click', function () {
        const rows = document.querySelectorAll('.attendance-activity-row');

        rows.forEach(function (row) {
            row.style.display = '';
        });

        const filterLabel = document.getElementById('attendanceFilterLabel');
        const rowCount = document.getElementById('attendanceRowCount');

        if (filterLabel) {
            filterLabel.textContent = "Showing today's fingerprint scans";
        }

        if (rowCount) {
            rowCount.textContent = rows.length + ' scan(s) recorded today';
        }
    });
}

        });
    </script>



    {{-- ACCOUNT WITHOUT STAFF PROFILE --}}
    @else
        <section class="rounded-2xl border border-amber-200 bg-white p-6 text-center shadow-sm sm:p-10">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10.3 3.8 2.9 17a2 2 0 001.7 3h14.8a2 2 0 001.7-3L13.7 3.8a2 2 0 00-3.4 0Z"/>
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-bold text-gray-900">Staff profile not linked</h2>
            <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-gray-500">
                Your user account is not currently linked to a staff profile.
                Please contact the system administrator.
            </p>
        </section>
    @endif

</div>
@endsection
```