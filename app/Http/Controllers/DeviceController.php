<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        return Device::orderBy('device_name')->paginate(20);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'device_name' => 'required|string|max:100',
            'device_uid'  => 'required|string|max:100|unique:devices,device_uid',
            'location'    => 'nullable|string|max:150',
            'ip_address'  => 'nullable|ip|max:45',
            'status'      => 'nullable|in:active,inactive,maintenance',
            'last_sync'   => 'nullable|date',
        ]);

        return response()->json(Device::create($data), 201);
    }

    public function show(Device $device)
    {
        return $device->loadCount(['fingerprintTemplates', 'attendanceRecords']);
    }

    public function update(Request $request, Device $device)
    {
        $data = $request->validate([
            'device_name' => 'sometimes|required|string|max:100',
            'device_uid'  => 'sometimes|required|string|max:100|unique:devices,device_uid,' . $device->id,
            'location'    => 'nullable|string|max:150',
            'ip_address'  => 'nullable|ip|max:45',
            'status'      => 'nullable|in:active,inactive,maintenance',
            'last_sync'   => 'nullable|date',
        ]);

        $device->update($data);

        return $device;
    }

    /**
     * Called by the ESP32 firmware on each successful sync to update its heartbeat.
     */
    public function heartbeat(Device $device)
    {
        $device->update(['last_sync' => now(), 'status' => 'active']);

        return $device;
    }

    public function destroy(Device $device)
    {
        $device->delete();

        return response()->json(null, 204);
    }
}
