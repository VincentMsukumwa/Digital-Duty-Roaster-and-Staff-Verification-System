<?php

namespace App\Http\Controllers;

use App\Models\FingerprintTemplate;
use Illuminate\Http\Request;

class FingerprintTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = FingerprintTemplate::with('staff:id,first_name,last_name,staff_no');

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        return $query->orderByDesc('enrolled_at')->paginate(20);
    }

    /**
     * The ESP32 enrolment flow posts the captured template as a base64 string
     * in `template_data`; we decode it before persisting as BYTEA.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_id'        => 'required|exists:staff,id',
            'device_id'       => 'nullable|exists:devices,id',
            'template_data'   => 'required|string',
            'finger_position' => 'nullable|string|max:30',
        ]);

        $data['template_data'] = base64_decode($data['template_data']);
        $data['enrolled_at'] = now();

        return response()->json(FingerprintTemplate::create($data), 201);
    }

    public function show(FingerprintTemplate $fingerprintTemplate)
    {
        return [
            'id'              => $fingerprintTemplate->id,
            'staff_id'        => $fingerprintTemplate->staff_id,
            'device_id'       => $fingerprintTemplate->device_id,
            'finger_position' => $fingerprintTemplate->finger_position,
            'enrolled_at'     => $fingerprintTemplate->enrolled_at,
            // template_data is deliberately omitted from API responses —
            // it's a biometric secret, not display data.
        ];
    }

    public function destroy(FingerprintTemplate $fingerprintTemplate)
    {
        $fingerprintTemplate->delete();

        return response()->json(null, 204);
    }
}
