
@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')

    {{-- Page header and filters --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">

        <div>
            <h2 class="text-xl font-semibold text-gray-800">
                System Audit Logs
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Monitor activities and changes performed within the system.
            </p>
        </div>

        <form method="GET" action="{{ route('audit-logs.index') }}">
            <select
                name="table_affected"
                onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
                <option value="">All Tables</option>

                @foreach ($tables as $table)
                    <option
                        value="{{ $table }}"
                        @selected(request('table_affected') === $table)
                    >
                        {{ ucfirst(str_replace('_', ' ', $table)) }}
                    </option>
                @endforeach
            </select>
        </form>

    </div>

    {{-- Read-only information --}}
    <div class="flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 mb-5">
        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none"
             stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4m0 4h.01M5.5 19h13a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.77 16a2 2 0 001.73 3z"/>
        </svg>

        <p class="text-xs text-blue-700">
            Read-only audit records. Audit entries cannot be edited or deleted (NFR-03).
        </p>
    </div>

    {{-- Audit logs table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm text-left">

                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-5 py-4 font-semibold whitespace-nowrap">When</th>
                        <th class="px-5 py-4 font-semibold">User / Source</th>
                        <th class="px-5 py-4 font-semibold">Action</th>
                        <th class="px-5 py-4 font-semibold">Table</th>
                        <th class="px-5 py-4 font-semibold">Record ID</th>
                        <th class="px-5 py-4 font-semibold">IP Address</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse ($auditLogs as $log)

                        @php
                            $action = strtoupper($log->action);

                            $badgeClass = match (true) {
                                str_contains($action, 'CREATED') ||
                                str_contains($action, 'COMPLETED')
                                    => 'bg-green-100 text-green-700',

                                str_contains($action, 'UPDATED')
                                    => 'bg-blue-100 text-blue-700',

                                str_contains($action, 'DELETED') ||
                                str_contains($action, 'FAILED')
                                    => 'bg-red-100 text-red-700',

                                str_contains($action, 'ARCHIVED')
                                    => 'bg-amber-100 text-amber-700',

                                str_contains($action, 'PUBLISHED')
                                    => 'bg-indigo-100 text-indigo-700',

                                str_contains($action, 'ATTENDANCE') ||
                                str_contains($action, 'SCAN')
                                    => 'bg-purple-100 text-purple-700',

                                str_contains($action, 'ENROLLMENT')
                                    => 'bg-teal-100 text-teal-700',

                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp

                        <tr class="hover:bg-gray-50 transition-colors">

                            {{-- Date and time --}}
                            <td class="px-5 py-4 whitespace-nowrap text-gray-600">
                                <div class="font-medium text-gray-800">
                                    {{ $log->created_at->format('d M Y') }}
                                </div>

                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $log->created_at->format('H:i:s') }}
                                </div>
                            </td>

                            {{-- User --}}
                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-800">
                                    {{ $log->user->username ?? 'Device / API' }}
                                </span>
                            </td>

                            {{-- Action --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                    {{ str_replace('_', ' ', $action) }}
                                </span>
                            </td>

                            {{-- Table --}}
                            <td class="px-5 py-4">
                                <span class="font-mono text-xs text-gray-600">
                                    {{ $log->table_affected ?? '—' }}
                                </span>
                            </td>

                            {{-- Record ID --}}
                            <td class="px-5 py-4 text-gray-600">
                                {{ $log->record_id ?? '—' }}
                            </td>

                            {{-- IP Address --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs text-gray-500">
                                    {{ $log->ip_address ?? '—' }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">

                                <div class="flex flex-col items-center">

                                    <svg class="w-10 h-10 text-gray-300 mb-3"
                                         fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="1.5"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>

                                    <p class="text-gray-500 font-medium">
                                        No audit records found.
                                    </p>

                                    <p class="text-xs text-gray-400 mt-1">
                                        System activities will appear here.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{-- Pagination --}}
    <div class="mt-5">
        {{ $auditLogs->links() }}
    </div>

@endsection
