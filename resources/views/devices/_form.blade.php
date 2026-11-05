@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-5">

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Device Name</label>
        <input type="text" name="device_name" value="{{ old('device_name', $device?->device_name) }}" required
               placeholder="e.g. Ward 3 Entrance"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Device UID</label>
        <input type="text" name="device_uid" value="{{ old('device_uid', $device?->device_uid) }}" required
               placeholder="ESP32 MAC address or serial"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-gray-400">(optional)</span></label>
        <input type="text" name="location" value="{{ old('location', $device?->location) }}"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">IP Address <span class="text-gray-400">(optional)</span></label>
        <input type="text" name="ip_address" value="{{ old('ip_address', $device?->ip_address) }}"
               placeholder="e.g. 192.168.1.50"
               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            @foreach (['active', 'inactive', 'maintenance'] as $option)
                <option value="{{ $option }}" @selected(old('status', $device?->status ?? 'active') === $option)>
                    {{ ucfirst($option) }}
                </option>
            @endforeach
        </select>
    </div>

    @if ($device)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Sync</label>
            <p class="text-sm text-gray-500 py-2">
                {{ $device->last_sync?->format('d M Y H:i') ?? 'Never — this is set automatically when the device calls its heartbeat endpoint.' }}
            </p>
        </div>
    @endif

</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="bg-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-full hover:bg-blue-700 transition">
        {{ $device ? 'Save Changes' : 'Add Device' }}
    </button>
    <a href="{{ route('devices.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
</div>
