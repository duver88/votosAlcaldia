<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\VoterController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\LogController;

// Redirect root to login
Route::get('/', fn() => redirect()->route('login'));

// Unified Login (voters and admins)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Unified Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Change Password (requires auth but not password changed)
Route::middleware(['auth', 'not.blocked'])->group(function () {
    Route::get('/change-password', [ChangePasswordController::class, 'showChangeForm'])->name('change-password');
    Route::post('/change-password', [ChangePasswordController::class, 'change']);
});

// Voting Routes (requires auth, password changed, not blocked, voting open)
Route::middleware(['auth', 'not.blocked', 'password.changed', 'voting.open'])->group(function () {
    Route::get('/vote', [VoteController::class, 'index'])->name('vote');
    Route::post('/vote', [VoteController::class, 'store'])->name('vote.store');
});

// Vote Confirmation (doesn't require voting.open - user already voted)
Route::middleware(['auth', 'not.blocked', 'password.changed'])->group(function () {
    Route::get('/vote/confirmation', [VoteController::class, 'confirmation'])->name('vote.confirmation');
});

Route::get('/voting-closed', [VoteController::class, 'closed'])->name('voting.closed');

// Admin Panel
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('admin.dashboard.stats');

    // Candidates
    Route::resource('candidates', CandidateController::class)->names([
        'index' => 'admin.candidates.index',
        'create' => 'admin.candidates.create',
        'store' => 'admin.candidates.store',
        'edit' => 'admin.candidates.edit',
        'update' => 'admin.candidates.update',
        'destroy' => 'admin.candidates.destroy',
    ]);
    Route::post('/candidates/{candidate}/toggle', [CandidateController::class, 'toggleActive'])->name('admin.candidates.toggle');
    Route::post('/candidates/order', [CandidateController::class, 'updateOrder'])->name('admin.candidates.order');

    // Voters
    Route::get('/voters', [VoterController::class, 'index'])->name('admin.voters.index');
    Route::get('/voters/create', [VoterController::class, 'create'])->name('admin.voters.create');
    Route::post('/voters', [VoterController::class, 'store'])->name('admin.voters.store');
    Route::delete('/voters/{voter}', [VoterController::class, 'destroy'])->name('admin.voters.destroy');
    Route::post('/voters/{voter}/unblock', [VoterController::class, 'unblock'])->name('admin.voters.unblock');
    Route::post('/voters/{voter}/reset-password', [VoterController::class, 'resetPassword'])->name('admin.voters.reset-password');
    Route::get('/voters/import', [VoterController::class, 'showImport'])->name('admin.voters.import');
    Route::post('/voters/import', [VoterController::class, 'import'])->name('admin.voters.import.process');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('/settings/toggle', [SettingsController::class, 'toggleVoting'])->name('admin.settings.toggle');

    // Logs
    Route::get('/logs', [LogController::class, 'index'])->name('admin.logs');
});
