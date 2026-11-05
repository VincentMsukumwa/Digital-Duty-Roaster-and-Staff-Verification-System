<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return Setting::orderBy('key')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key'   => 'required|string|max:100|unique:settings,key',
            'value' => 'nullable|string',
        ]);

        return response()->json(Setting::create($data), 201);
    }

    public function show(Setting $setting)
    {
        return $setting;
    }

    /**
     * Settings are looked up by key rather than id in most admin screens.
     */
    public function showByKey(string $key)
    {
        $setting = Setting::where('key', $key)->firstOrFail();

        return $setting;
    }

    public function update(Request $request, Setting $setting)
    {
        $data = $request->validate([
            'value' => 'nullable|string',
        ]);

        $setting->update($data);

        return $setting;
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();

        return response()->json(null, 204);
    }
}
