<?php

namespace App\Http\Controllers;

use App\Models\ReportsLog;
use Illuminate\Http\Request;

class ReportsLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportsLog::with('generatedBy:id,username');

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        return $query->orderByDesc('created_at')->paginate(20);
    }

    /**
     * Records that a report was generated (e.g. a ghost worker or absenteeism
     * report exported by a supervisor/HR admin) and where the file lives.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'report_type'      => 'required|string|max:100',
            'date_range_start' => 'nullable|date',
            'date_range_end'   => 'nullable|date|after_or_equal:date_range_start',
            'file_path'        => 'nullable|string|max:255',
        ]);

        $data['generated_by'] = $request->user()?->id;

        return response()->json(ReportsLog::create($data), 201);
    }

    public function show(ReportsLog $reportsLog)
    {
        return $reportsLog->load('generatedBy');
    }

    public function destroy(ReportsLog $reportsLog)
    {
        $reportsLog->delete();

        return response()->json(null, 204);
    }
}
