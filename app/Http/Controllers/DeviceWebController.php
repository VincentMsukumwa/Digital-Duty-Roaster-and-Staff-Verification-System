<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Device;
use Illuminate\Http\Request;

class DeviceWebController extends Controller
{
    public function index()
    {
        $devices = Device::orderBy('device_name')->paginate(15);

        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'device_name' => 'required|string|max:100',
            'device_uid'  => 'required|string|max:100|unique:devices,device_uid',
            'location'    => 'nullable|string|max:150',
            'ip_address'  => 'nullable|ip|max:45',
            'status'      => 'nullable|in:active,inactive,maintenance',
        ]);

        $device = Device::create($data);

        // Log device creation.
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'DEVICE_CREATED',
            'table_affected' => 'devices',
            'record_id'      => $device->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('devices.index')
            ->with('status', 'Device added.');
    }

    public function edit(Device $device)
    {
        return view('devices.edit', compact('device'));
    }

    public function update(Request $request, Device $device)
    {
        $data = $request->validate([
            'device_name' => 'required|string|max:100',
            'device_uid'  => 'required|string|max:100|unique:devices,device_uid,' . $device->id,
            'location'    => 'nullable|string|max:150',
            'ip_address'  => 'nullable|ip|max:45',
            'status'      => 'nullable|in:active,inactive,maintenance',
        ]);

        $device->update($data);

        // Log device update.
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'DEVICE_UPDATED',
            'table_affected' => 'devices',
            'record_id'      => $device->id,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('devices.index')
            ->with('status', 'Device updated.');
    }

    public function destroy(Request $request, Device $device)
    {
        $deviceId = $device->id;

        $device->delete();

        // Log only after successful deletion.
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => 'DEVICE_DELETED',
            'table_affected' => 'devices',
            'record_id'      => $deviceId,
            'ip_address'     => $request->ip(),
        ]);

        return redirect()
            ->route('devices.index')
            ->with('status', 'Device deleted.');
    }
}
