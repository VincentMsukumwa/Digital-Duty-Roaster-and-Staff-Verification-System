<?php

use App\Http\Controllers\AttendanceWebController;
use App\Http\Controllers\AuditLogWebController;
use App\Http\Controllers\DepartmentWebController;
use App\Http\Controllers\DeviceWebController;
use App\Http\Controllers\LeaveRequestWebController;
use App\Http\Controllers\RoleWebController;
use App\Http\Controllers\RosterWebController;
use App\Http\Controllers\SettingWebController;
use App\Http\Controllers\StaffWebController;
use App\Http\Controllers\UserWebController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordSetupController;

/*
|--------------------------------------------------------------------------
| Web Routes — Blade UI (session-based, separate from routes/api.php)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/dashboard');
});

/// Guest (not logged in)
Route::middleware('guest')->group(function () {

    // Login
    Route::get('/login', [WebAuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [WebAuthController::class, 'login']);

    // Password setup / reset
    Route::get(
        '/password/setup/{token}',
        [PasswordSetupController::class, 'showResetForm']
    )->name('password.reset');

    Route::post(
        '/password/setup',
        [PasswordSetupController::class, 'resetPassword']
    )->name('password.update');
});

// Authenticated users
Route::middleware('auth')->group(function () {

    Route::post('/logout', [WebAuthController::class, 'logout']);

    Route::get('reports', [\App\Http\Controllers\ReportWebController::class, 'index'])
        ->name('reports.index');

    // Dashboard
    Route::get('/dashboard', function () {
        $user = request()->user();
        $role = $user->role->name ?? null;
        $staff = $user->staff;

        $todayRoster = null;
        $todayAttendance = collect();

        // Staff dashboard data
        if ($staff) {
            $todayRoster = \App\Models\Roster::with('department')
                ->where('staff_id', $staff->id)
                ->whereDate('effective_date', today())
                ->whereIn('status', ['draft', 'published'])
                ->orderByDesc('effective_date')
                ->first();

            $todayAttendance = \App\Models\AttendanceRecord::with('device')
                ->where('staff_id', $staff->id)
                ->whereDate('scan_date', today())
                ->orderBy('scan_time')
                ->get();
        }

        // Today's attendance scans grouped by hour for the graph
        $attendanceByHour = \App\Models\AttendanceRecord::query()
            ->whereDate('scan_date', today())
            ->selectRaw('EXTRACT(HOUR FROM scan_time)::integer AS hour')
            ->selectRaw('COUNT(*) AS total')
            ->groupByRaw('EXTRACT(HOUR FROM scan_time)')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $attendanceChart = collect(range(0, 23))->map(function ($hour) use ($attendanceByHour) {
            return [
                'hour' => sprintf('%02d:00', $hour),
                'total' => (int) ($attendanceByHour->get($hour)->total ?? 0),
            ];
        });

        // Today's attendance activity
        $attendanceActivity = \App\Models\AttendanceRecord::with([
            'staff',
            'device',
        ])
            ->whereDate('scan_date', today())
            ->orderByDesc('scan_time')
            ->get();

        return view('dashboard', compact(
            'user',
            'role',
            'staff',
            'todayRoster',
            'todayAttendance',
            'attendanceChart',
            'attendanceActivity'
        ));
    })->name('dashboard');


    // Staff & Departments — System Administrator + HR Administrator
    Route::middleware('role:System Administrator,HR Administrator')->group(function () {

        // Archived staff and recovery routes
        Route::get('staff/archived', [StaffWebController::class, 'archived'])
            ->name('staff.archived');

        Route::post('staff/{id}/restore', [StaffWebController::class, 'restore'])
            ->name('staff.restore');

        // Standard staff management
        Route::resource('staff', StaffWebController::class)->except(['show']);

        // Individual staff attendance statistics
        Route::get(
            'staff/{staff}/statistics',
            [StaffWebController::class, 'statistics']
        )->name('staff.statistics');

        // Fingerprint enrollment
        Route::post(
            'staff/{staff}/fingerprint/start',
            [StaffWebController::class, 'startFingerprintRegistration']
        )->name('staff.fingerprint.start');

        // Department management
        Route::resource('departments', DepartmentWebController::class)->except(['show']);
    });


    // Rosters — viewing available to authenticated users
    Route::get('rosters', [RosterWebController::class, 'index'])
        ->name('rosters.index');

    Route::middleware('role:System Administrator,Supervisor / Manager')->group(function () {

        Route::get('rosters/create', [RosterWebController::class, 'create'])
            ->name('rosters.create');

        Route::post('rosters', [RosterWebController::class, 'store'])
            ->name('rosters.store');

        Route::get('rosters/{roster}/edit', [RosterWebController::class, 'edit'])
            ->name('rosters.edit');

        Route::put('rosters/{roster}', [RosterWebController::class, 'update'])
            ->name('rosters.update');

        Route::delete('rosters/{roster}', [RosterWebController::class, 'destroy'])
            ->name('rosters.destroy');

        Route::post('rosters/{roster}/publish', [RosterWebController::class, 'publish'])
            ->name('rosters.publish');

        Route::post('rosters/{roster}/archive', [RosterWebController::class, 'archive'])
            ->name('rosters.archive');

        // Bulk roster scheduling
        Route::get('rosters/bulk/create', [RosterWebController::class, 'bulkCreate'])
            ->name('rosters.bulk.create');

        Route::get('rosters/bulk/staff', [RosterWebController::class, 'bulkStaff'])
            ->name('rosters.bulk.staff');

        Route::post('rosters/bulk', [RosterWebController::class, 'bulkStore'])
            ->name('rosters.bulk.store');
    });


    // Attendance
    Route::get('attendance', [AttendanceWebController::class, 'index'])
        ->name('attendance.index');

    Route::middleware('role:System Administrator,Supervisor / Manager')->group(function () {
        Route::get(
            'attendance/staff/{staff}',
            [AttendanceWebController::class, 'staffHistory']
        )->name('attendance.staff-history');
    });

    Route::middleware('role:System Administrator')->group(function () {
        Route::delete(
            'attendance/{attendance_record}',
            [AttendanceWebController::class, 'destroy']
        )->name('attendance.destroy');
    });


    // Leave Requests
    Route::get('leave-requests', [LeaveRequestWebController::class, 'index'])
        ->name('leave-requests.index');

    Route::get('leave-requests/create', [LeaveRequestWebController::class, 'create'])
        ->name('leave-requests.create');

    Route::post('leave-requests', [LeaveRequestWebController::class, 'store'])
        ->name('leave-requests.store');

    Route::delete('leave-requests/{leaveRequest}', [LeaveRequestWebController::class, 'destroy'])
        ->name('leave-requests.destroy');

    Route::middleware('role:System Administrator,Supervisor / Manager')->group(function () {
        Route::post('leave-requests/{leaveRequest}/approve', [LeaveRequestWebController::class, 'approve'])
            ->name('leave-requests.approve');

        Route::post('leave-requests/{leaveRequest}/reject', [LeaveRequestWebController::class, 'reject'])
            ->name('leave-requests.reject');
    });


    // Users, Roles, Devices, Settings, Audit Logs — System Administrator only
    Route::middleware('role:System Administrator')->group(function () {

        Route::resource('users', UserWebController::class)->except(['show']);
        Route::resource('roles', RoleWebController::class)->except(['show']);
        Route::resource('devices', DeviceWebController::class)->except(['show']);

        Route::get('settings', [SettingWebController::class, 'index'])
            ->name('settings.index');

        Route::get('settings/{setting}/edit', [SettingWebController::class, 'edit'])
            ->name('settings.edit');

        Route::put('settings/{setting}', [SettingWebController::class, 'update'])
            ->name('settings.update');

        Route::get('audit-logs', [AuditLogWebController::class, 'index'])
            ->name('audit-logs.index');
    });

});
