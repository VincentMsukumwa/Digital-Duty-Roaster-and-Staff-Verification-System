
@php
    $role = auth()->user()->role->name ?? null;

    $isHR = in_array($role, ['HR Administrator', 'System Administrator']);
    $isAdmin = $role === 'System Administrator';

    $linkBase = 'group flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors duration-200';
    $linkActive = 'bg-gray-800 text-white font-medium';
    $linkInactive = 'text-gray-400 hover:bg-gray-800/70 hover:text-white';

    $iconBase = 'w-5 h-5 flex-shrink-0';
@endphp

<aside id="mobileSidebar"
       class="fixed inset-y-0 left-0 z-50 w-64
              bg-[#111318] text-gray-300
              flex flex-col
              transform -translate-x-full lg:translate-x-0
              transition-transform duration-300 ease-in-out
              border-r border-white/5">

    {{-- BRAND --}}
    <div class="px-5 py-5 border-b border-white/10">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 min-w-0"
               onclick="closeSidebar()">

                <div class="w-9 h-9 rounded-xl bg-indigo-500
                            flex items-center justify-center
                            text-white font-bold text-lg flex-shrink-0">
                    Q
                </div>

                <div class="min-w-0">
                    <div class="text-white font-semibold tracking-wide">
                        QECH
                    </div>
                    <div class="text-[11px] text-gray-500 truncate">
                        Staff Management System
                    </div>
                </div>
            </a>

            <button type="button"
                    onclick="closeSidebar()"
                    class="lg:hidden w-8 h-8 rounded-lg
                           flex items-center justify-center
                           text-gray-400 hover:text-white hover:bg-gray-800"
                    aria-label="Close navigation">

                <svg class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- NAVIGATION --}}
    <nav class="flex-1 px-3 py-5 overflow-y-auto space-y-1">

        {{-- MAIN MENU --}}
        <p class="px-3 mb-2 text-[10px] font-semibold
                  uppercase tracking-[0.16em] text-gray-500">
            Main Menu
        </p>

        {{-- DASHBOARD --}}
        <a href="{{ route('dashboard') }}"
           onclick="closeSidebar()"
           @class([$linkBase, $linkActive => request()->routeIs('dashboard'),
                   $linkInactive => !request()->routeIs('dashboard')])>
            <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="1.7">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            <span>Dashboard</span>
        </a>

        {{-- ROSTERS --}}
        @if (Route::has('rosters.index'))
            <a href="{{ route('rosters.index') }}"
               onclick="closeSidebar()"
               @class([$linkBase, $linkActive => request()->routeIs('rosters.*'),
                       $linkInactive => !request()->routeIs('rosters.*')])>
                <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="1.7">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m8 15 2 2 5-4"/>
                </svg>
                <span>Duty Rosters</span>
            </a>
        @endif

        {{-- ATTENDANCE --}}
        @if (Route::has('attendance.index'))
            <a href="{{ route('attendance.index') }}"
               onclick="closeSidebar()"
               @class([$linkBase, $linkActive => request()->routeIs('attendance.*'),
                       $linkInactive => !request()->routeIs('attendance.*')])>
                <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="1.7">
                    <circle cx="12" cy="12" r="9"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m8 12 2.5 2.5L16 9"/>
                </svg>
                <span>Attendance</span>
            </a>
        @endif

        {{-- LEAVE REQUESTS --}}
        @if (Route::has('leave-requests.index'))
            <a href="{{ route('leave-requests.index') }}"
               onclick="closeSidebar()"
               @class([$linkBase, $linkActive => request()->routeIs('leave-requests.*'),
                       $linkInactive => !request()->routeIs('leave-requests.*')])>
                <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="1.7">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                    <path stroke-linecap="round" d="M8 14h3M8 17h6"/>
                </svg>
                <span>Leave Requests</span>
            </a>
        @endif

        {{-- HR MANAGEMENT --}}
        @if ($isHR)
            <p class="px-3 pt-6 pb-2 text-[10px] font-semibold
                      uppercase tracking-[0.16em] text-gray-500">
                HR Management
            </p>

            {{-- STAFF --}}
            @if (Route::has('staff.index'))
                <a href="{{ route('staff.index') }}"
                   onclick="closeSidebar()"
                   @class([$linkBase, $linkActive => request()->routeIs('staff.*'),
                           $linkInactive => !request()->routeIs('staff.*')])>
                    <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="10" cy="7" r="4"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Staff</span>
                </a>
            @endif

            {{-- DEPARTMENTS --}}
            @if (Route::has('departments.index'))
                <a href="{{ route('departments.index') }}"
                   onclick="closeSidebar()"
                   @class([$linkBase, $linkActive => request()->routeIs('departments.*'),
                           $linkInactive => !request()->routeIs('departments.*')])>
                    <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
                        <path stroke-linecap="round"
                              d="M8 9v1M8 13v1M8 17v1M15 13v1M15 17v1"/>
                    </svg>
                    <span>Departments</span>
                </a>
            @endif

            {{-- REPORTS LOG --}}
            @if (Route::has('reports-log.index'))
                <a href="{{ route('reports-log.index') }}"
                   onclick="closeSidebar()"
                   @class([$linkBase, $linkActive => request()->routeIs('reports-log.*'),
                           $linkInactive => !request()->routeIs('reports-log.*')])>
                    <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
                    </svg>
                    <span>Reports Log</span>
                </a>
            @endif
        @endif

        {{-- ADMINISTRATION --}}
        @if ($isAdmin)
            <p class="px-3 pt-6 pb-2 text-[10px] font-semibold
                      uppercase tracking-[0.16em] text-gray-500">
                Administration
            </p>

            {{-- USERS --}}
            @if (Route::has('users.index'))
                <a href="{{ route('users.index') }}"
                   onclick="closeSidebar()"
                   @class([$linkBase, $linkActive => request()->routeIs('users.*'),
                           $linkInactive => !request()->routeIs('users.*')])>
                    <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="10" cy="7" r="4"/>
                        <path stroke-linecap="round" d="M19 8v6M16 11h6"/>
                    </svg>
                    <span>Users</span>
                </a>
            @endif

            {{-- ROLES --}}
            @if (Route::has('roles.index'))
                <a href="{{ route('roles.index') }}"
                   onclick="closeSidebar()"
                   @class([$linkBase, $linkActive => request()->routeIs('roles.*'),
                           $linkInactive => !request()->routeIs('roles.*')])>
                    <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 3 5 6v5c0 4.5 2.8 8 7 10 4.2-2 7-5.5 7-10V6l-7-3Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m9 12 2 2 4-4"/>
                    </svg>
                    <span>Roles</span>
                </a>
            @endif

            {{-- DEVICES --}}
            @if (Route::has('devices.index'))
                <a href="{{ route('devices.index') }}"
                   onclick="closeSidebar()"
                   @class([$linkBase, $linkActive => request()->routeIs('devices.*'),
                           $linkInactive => !request()->routeIs('devices.*')])>
                    <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <rect x="4" y="3" width="16" height="13" rx="2"/>
                        <path stroke-linecap="round" d="M8 21h8M12 16v5"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m9 9 2 2 4-4"/>
                    </svg>
                    <span>Devices</span>
                </a>
            @endif

            {{-- REPORTS --}}
            @if (Route::has('reports.index'))
                <a href="{{ route('reports.index') }}"
                   onclick="closeSidebar()"
                   @class([$linkBase, $linkActive => request()->routeIs('reports.index'),
                           $linkInactive => !request()->routeIs('reports.index')])>
                    <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3v18h18"/>
                        <path stroke-linecap="round" d="M8 17v-4M13 17V7M18 17v-8"/>
                    </svg>
                    <span>Reports</span>
                </a>
            @endif

            {{-- SETTINGS --}}
            @if (Route::has('settings.index'))
                <a href="{{ route('settings.index') }}"
                   onclick="closeSidebar()"
                   @class([$linkBase, $linkActive => request()->routeIs('settings.*'),
                           $linkInactive => !request()->routeIs('settings.*')])>
                    <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <circle cx="12" cy="12" r="3"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1-1.8 3-1.7-.7a8 8 0 0 1-2 .8l-.3 1.8h-3.5l-.3-1.8a8 8 0 0 1-2-.8l-1.7.7-1.8-3 .1-.1a1.7 1.7 0 0 0 .3-1.9l-1.7-1 .9-3.4 2-.1a8 8 0 0 1 1.4-1.4l-.1-2 3.4-.9 1 1.7a8 8 0 0 1 2 0l1-1.7 3.4.9-.1 2a8 8 0 0 1 1.4 1.4l2 .1.9 3.4-1.7 1Z"
                              transform="translate(-1 -1) scale(1.08)"/>
                    </svg>
                    <span>Settings</span>
                </a>
            @endif

            {{-- AUDIT LOGS --}}
            @if (Route::has('audit-logs.index'))
                <a href="{{ route('audit-logs.index') }}"
                   onclick="closeSidebar()"
                   @class([$linkBase, $linkActive => request()->routeIs('audit-logs.*'),
                           $linkInactive => !request()->routeIs('audit-logs.*')])>
                    <svg class="{{ $iconBase }}" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 2M21 12a9 9 0 1 1-3-6.7"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 3v6h-6"/>
                    </svg>
                    <span>Audit Logs</span>
                </a>
            @endif
        @endif
    </nav>

    {{-- USER PROFILE FOOTER --}}
    <div class="p-3 border-t border-white/10">
        <div class="rounded-xl p-3 hover:bg-gray-800/70 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gray-700
                            flex items-center justify-center
                            text-white text-sm font-semibold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-200 truncate">
                        {{ auth()->user()->username ?? 'User' }}
                    </p>
                    <p class="text-xs text-gray-500 truncate">
                        {{ $role ?? 'Staff Member' }}
                    </p>
                </div>

                <svg class="w-4 h-4 text-gray-500 flex-shrink-0"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="m9 18 6-6-6-6"/>
                </svg>
            </div>
        </div>

        <p class="px-3 pt-2 text-[10px] text-gray-600">
            QECH · Malawi
        </p>
    </div>

</aside>
