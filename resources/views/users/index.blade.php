@extends('layouts.app')

@section('title', 'User Accounts')

@section('content')

    <div class="flex items-center justify-end mb-5">
        <a href="{{ route('users.create') }}"
           class="bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-blue-700 transition">
            + Add User
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
                    <th class="px-4 py-3">Username</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Linked Staff</th>
                    <th class="px-4 py-3">Last Login</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $account)
                    <tr class="hover:bg-blue-50/40">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $account->username }}</td>
                        <td class="px-4 py-3">{{ $account->email }}</td>
                        <td class="px-4 py-3">
                            @if ($account->role)
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ $account->role->name }}
                                </span>
                            @else
                                <span class="text-gray-400">— None —</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($account->staff)
                                {{ $account->staff->first_name }} {{ $account->staff->last_name }}
                            @else
                                <span class="text-yellow-600 text-xs">Not linked</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $account->last_login?->format('d M Y H:i') ?? 'Never' }}
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('users.edit', $account) }}" class="text-blue-600 hover:underline">Edit</a>
                            @if ($account->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $account) }}" class="inline"
                                      onsubmit="return confirm('Delete the login for {{ $account->username }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">No user accounts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

@endsection
