<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogWebController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user:id,username');

        if ($request->filled('table_affected')) {
            $query->where('table_affected', $request->table_affected);
        }

        $auditLogs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // For the filter dropdown — distinct table names actually present
        // in the log, rather than a hardcoded list that could drift out
        // of sync with what's really been recorded.
        $tables = AuditLog::query()
            ->whereNotNull('table_affected')
            ->distinct()
            ->orderBy('table_affected')
            ->pluck('table_affected');

        return view('audit-logs.index', compact('auditLogs', 'tables'));
    }
}
