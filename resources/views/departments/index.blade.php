@extends('layouts.app')

@section('title', 'Departments')

@section('content')

    <div class="flex items-center justify-end mb-5">
        <a href="{{ route('departments.create') }}"
           class="bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-blue-700 transition">
            + Add Department
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
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3">Staff</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($departments as $department)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $department->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $department->description ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                {{ $department->staff_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('departments.edit', $department) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('departments.destroy', $department) }}" class="inline"
                                  onsubmit="return confirm(
                                      {{ $department->staff_count > 0
                                          ? "'This department has ".$department->staff_count." staff member(s) — deleting it will unassign them, not delete them. Continue?'"
                                          : "'Delete this department?'" }}
                                  );">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">No departments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $departments->links() }}
    </div>

@endsection
