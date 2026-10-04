<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CounsellingRecordController;
use App\Http\Controllers\Counsellor\ReferralInboxController;
use App\Http\Controllers\Counsellor\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(auth()->check() ? '/dashboard' : '/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // No self-service account deletion: it would cascade-delete a counsellor's
    // records without an audit trail. Admins deactivate accounts instead.
});

// Counsellor: create/view/edit/delete/search their own student counselling records,
// plus the inbox of referrals raised by teachers.
Route::middleware(['auth', 'verified', 'role:counsellor'])->group(function () {
    Route::resource('records', CounsellingRecordController::class);
    Route::get('records/{record}/print', [CounsellingRecordController::class, 'print'])->name('records.print');
    Route::get('reports/caseload', [ReportController::class, 'caseload'])->name('reports.caseload');

    Route::prefix('referral-inbox')->name('referral-inbox.')->group(function () {
        Route::get('/', [ReferralInboxController::class, 'index'])->name('index');
        Route::get('{referral}', [ReferralInboxController::class, 'show'])->name('show');
        Route::patch('{referral}/status', [ReferralInboxController::class, 'updateStatus'])->name('status');
        Route::post('{referral}/convert', [ReferralInboxController::class, 'convert'])->name('convert');
    });
});

// Teacher: read-only student profile lookup, plus raising referrals and
// tracking their STATUS only — never the resulting counselling notes.
Route::middleware(['auth', 'verified', 'role:teacher'])->group(function () {
    Route::resource('students', StudentController::class)->only(['index', 'show']);
    Route::resource('referrals', ReferralController::class)->only(['index', 'create', 'store', 'show']);
});

// Admin: user account management and audit log oversight only — no access
// to counselling record content, per the RBAC design (Akta Kaunselor 1998).
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show', 'destroy']);
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::patch('users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
});

require __DIR__.'/auth.php';
