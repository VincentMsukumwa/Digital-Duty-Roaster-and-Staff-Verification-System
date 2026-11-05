@extends('layouts.app')

@section('title', 'System Settings')

@section('content')

    @if (session('status'))
        <div class="bg-green-50 border border-green-100 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-blue-50 shadow-sm shadow-blue-100/50 overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">Key</th>
                    <th class="px-4 py-3">Value</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($settings as $setting)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $setting->key }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $setting->value }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('settings.edit', $setting) }}" class="text-blue-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">No settings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection
