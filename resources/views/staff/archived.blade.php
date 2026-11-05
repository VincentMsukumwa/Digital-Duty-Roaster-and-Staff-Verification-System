
@extends('layouts.app')

@section('title', 'Archived Staff')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
    <div>
        <h2 class="text-xl font-bold text-gray-800">Archived Staff</h2>
        <p class="text-sm text-gray-500 mt-1">
            View and restore previously archived staff members.
        </p>
    </div>

    <a href="{{ route('staff.index') }}"
       class="text-sm text-blue-600 hover:underline">
        &larr; Back to Staff
    </a>
</div>

{{-- Success message --}}
@if (session('status'))
    <div class="bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
        {{ session('status') }}
    </div>
@endif

{{-- Error message --}}
@if (session('error'))
    <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
        {{ session('error') }}
    </div>
@endif

{{-- Archived staff table --}}
<div class="bg-white rounded-2xl border border-blue-50 shadow-sm shadow-blue-100/50 overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">

            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Staff No</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Department</th>
                    <th class="px-4 py-3">Position</th>
                    <th class="px-4 py-3">Date Archived</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse ($staff as $member)

                    <tr class="hover:bg-blue-50/40">

                        <td class="px-4 py-3">
                            {{ $member->staff_no }}
                        </td>

                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $member->first_name }}
                            {{ $member->last_name }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $member->department->name ?? '—' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ $member->position ?? '—' }}
                        </td>

                        <td class="px-4 py-3 text-gray-500">
                            {{ $member->deleted_at?->format('d M Y, H:i') ?? '—' }}
                        </td>

                        <td class="px-4 py-3 text-right">

                            <form method="POST"
                                  action="{{ route('staff.restore', $member->id) }}"
                                  class="inline"
                                  onsubmit="return confirm('Restore {{ $member->first_name }} {{ $member->last_name }}?');">

                                @csrf

                                <button type="submit"
                                        class="text-green-600 font-medium hover:underline">
                                    Restore
                                </button>

                            </form>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6"
                            class="px-4 py-8 text-center text-gray-400">
                            No archived staff members found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
    </div>
</div>

{{-- Pagination --}}
<div class="mt-4">
    {{ $staff->links() }}
</div>

@endsection
