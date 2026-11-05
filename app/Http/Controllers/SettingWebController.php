<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingWebController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('key')->get();

        return view('settings.index', compact('settings'));
    }

    public function edit(Setting $setting)
    {
        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request, Setting $setting)
    {
        $data = $request->validate([
            'value' => 'nullable|string',
        ]);

        $setting->update($data);

        return redirect()->route('settings.index')->with('status', 'Setting updated.');
    }
}
