<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Rota raiz: redireciona para login
Route::get('/', fn () => redirect()->route('login'));

// Auth (sem middleware de auth)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Relatório público (sem login)
Route::get('/r/{token}', [ReportController::class, 'showShare'])->name('report.share');

// Rotas do Aluno
Route::middleware(['auth', 'check.user.status', 'check.access.expiry'])->group(function () {
    Route::get('/dashboard', [StudentDashboard::class, 'showDashboard'])->name('student.dashboard');
    Route::get('/projects/{id}/report/pdf', [ReportController::class, 'downloadPdf'])->name('report.pdf');

    // Wizard Livewire — rotas de página (o componente Livewire cuida do estado)
    Route::get('/projects', function () { return view('student.projects'); })->name('student.projects');
    Route::get('/projects/new', function () { return view('student.wizard', ['projectId' => null]); })->name('student.project.new');
    Route::get('/projects/{id}/edit', function (int $id) { return view('student.wizard', ['projectId' => $id]); })->name('student.project.edit');
});

// Rotas Admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'check.user.status', 'admin.only'])->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'showDashboard'])->name('dashboard');
    Route::get('/users', function () { return view('admin.users'); })->name('users');
    Route::get('/logs/login', function () { return view('admin.login-logs'); })->name('logs.login');
    Route::get('/logs/webhooks', function () { return view('admin.webhook-logs'); })->name('logs.webhooks');
});
