<?php

use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\DeviceScanController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FingerprintTemplateController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportsLogController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RosterController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceCommandController;

/*
|--------------------------------------------------------------------------
| API Routes — QECH Digital Duty Roster & Attendance System
|--------------------------------------------------------------------------
| Every group below is wrapped in Route::name('api.')->group(...), so
| every named route here gets an "api." prefix (e.g. api.users.index
| instead of just users.index).
|
| This is what prevents a name collision with routes/web.php, which uses
| the exact same resource names (users, staff, roles, rosters, etc.) for
| its own, separate, browser-facing pages. Route *names* only affect PHP
| code that calls route('some.name') — they don't change the actual URLs,
| which are still /api/users, /api/staff, and so on.
|
| Role names must match exactly what's seeded in the roles table:
|   System Administrator, HR Administrator, Supervisor / Manager,
|   Healthcare Worker
*/

// ── Public ──────────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

Route::post(
    'device/enrollment/start',
    [DeviceCommandController::class, 'startEnrollment']
);

Route::get(
    'device/{device}/command',
    [DeviceCommandController::class, 'getCommand']
);

Route::post(
    'device/{device}/command/{command}/complete',
    [DeviceCommandController::class, 'complete']
);

Route::post('device/scan', [DeviceScanController::class, 'scan']);

// ── Any authenticated user (role checked only where it matters) ─────────
Route::name('api.')->middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    

    // Duty rosters — every role can view (Healthcare Worker checks their
    // own schedule); only System Administrator / Supervisor can write.
    Route::get('rosters', [RosterController::class, 'index'])->name('rosters.index');
    Route::get('rosters/{roster}', [RosterController::class, 'show'])->name('rosters.show');

    // Attendance — created by the device/API pipeline once a staff member
    // is identified; viewing is open to any authenticated user.
    Route::apiResource('attendance-records', AttendanceRecordController::class)
        ->only(['index', 'store', 'show']);
    // destroy is intentionally NOT here — see the System Administrator
    // group below; the controller also logs the override itself (NFR-08).

    // Leave requests — any staff member can submit/view/edit their own;
    // approve/reject is restricted further down.
    Route::apiResource('leave-requests', LeaveRequestController::class)
        ->except(['destroy']);

    // Notifications — scoped to the authenticated user inside the controller.
    Route::apiResource('notifications', NotificationController::class)
        ->only(['index', 'store', 'show', 'destroy']);
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    // Staff — any authenticated user can view their own attendance history;
    // full staff CRUD is restricted further down.
    Route::get('staff/{staff}/attendance-history', [StaffController::class, 'attendanceHistory']);
    Route::get('staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
});

// ── System Administrator ─────────────────────────────────────────────────
// "Full system access: user management, configuration, and audit logs"
Route::name('api.')->middleware(['auth:sanctum', 'role:System Administrator'])->group(function () {
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('devices', DeviceController::class);
    Route::post('devices/{device}/heartbeat', [DeviceController::class, 'heartbeat']);
    Route::get('settings/key/{key}', [SettingController::class, 'showByKey']);
    Route::apiResource('settings', SettingController::class)->except(['destroy']);
    Route::get('audit-logs', [AuditLogController::class, 'index']);
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show']);
    Route::delete('attendance-records/{attendance_record}', [AttendanceRecordController::class, 'destroy'])
        ->name('attendance-records.destroy');
});

// ── System Administrator + HR Administrator ───────────────────────────────
// "Staff registration, fingerprint enrolment, workforce reports, ghost worker detection"
Route::name('api.')->middleware(['auth:sanctum', 'role:System Administrator,HR Administrator'])->group(function () {
    Route::apiResource('departments', DepartmentController::class);
    Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::patch('staff/{staff}', [StaffController::class, 'update']);
    Route::delete('staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
    Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
    Route::apiResource('fingerprint-templates', FingerprintTemplateController::class)
        ->only(['index', 'store', 'show', 'destroy']);
    Route::apiResource('reports-log', ReportsLogController::class)
        ->only(['index', 'store', 'show', 'destroy']);
});

// ── System Administrator + Supervisor / Manager ────────────────────────────
// "Roster creation and publication, attendance dashboard, absenteeism alerts"
Route::name('api.')->middleware(['auth:sanctum', 'role:System Administrator,Supervisor / Manager'])->group(function () {
    Route::post('rosters', [RosterController::class, 'store'])->name('rosters.store');
    Route::put('rosters/{roster}', [RosterController::class, 'update'])->name('rosters.update');
    Route::patch('rosters/{roster}', [RosterController::class, 'update']);
    Route::delete('rosters/{roster}', [RosterController::class, 'destroy'])->name('rosters.destroy');
    Route::post('rosters/{roster}/publish', [RosterController::class, 'publish'])->name('rosters.publish');
    Route::post('rosters/{roster}/archive', [RosterController::class, 'archive'])->name('rosters.archive');
    Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave-requests.reject');
});
