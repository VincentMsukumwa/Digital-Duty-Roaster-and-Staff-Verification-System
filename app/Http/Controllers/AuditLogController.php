<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

/**
 * Audit logs are immutable (NFR-03) — this controller is read-only.
 * Entries are written internally (e.g. via an AuditLog::create() call from
 * an action-logging middleware or trait), never through a public store route.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user:id,username');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('table_affected')) {
            $query->where('table_affected', $request->table_affected);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        return $query->orderByDesc('created_at')->paginate(30);
    }

    public function show(AuditLog $auditLog)
    {
        return $auditLog->load('user');
    }
}
