@extends('layouts.app')

@section('title', 'Devices')

@section('content')

    <div class="flex items-center justify-end mb-5">
        <a href="{{ route('devices.create') }}"
           class="bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-blue-700 transition">
            + Add Device
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
                    <th class="px-4 py-3">Device UID</th>
                    <th class="px-4 py-3">Location</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Last Sync</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($devices as $device)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $device->device_name }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $device->device_uid }}</td>
                        <td class="px-4 py-3">{{ $device->location ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span @class([
                                'px-3 py-1 rounded-full text-xs font-medium',
                                'bg-green-100 text-green-700' => $device->status === 'active',
                                'bg-gray-100 text-gray-600' => $device->status === 'inactive',
                                'bg-yellow-100 text-yellow-700' => $device->status === 'maintenance',
                            ])>
                                {{ ucfirst($device->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $device->last_sync?->format('d M Y H:i') ?? 'Never' }}
                        </td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('devices.edit', $device) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('devices.destroy', $device) }}" class="inline"
                                  onsubmit="return confirm('Delete this device?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">No devices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $devices->links() }}
    </div>

@endsection
