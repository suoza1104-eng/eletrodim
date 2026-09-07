<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookOutEvent;

class DashboardController extends Controller
{
    /**
     * Exibe o painel administrativo com métricas gerais.
     */
    public function showDashboard()
    {
        $totalStudents    = User::where('role', 'student')->count();
        $activeStudents   = User::where('role', 'student')->where('status', 'active')->count();
        $totalProjects    = Project::count();
        $completedProjects = Project::where('status', 'completed')->count();
        $recentLogins     = LoginLog::with('user')
            ->where('status', 'success')
            ->latest('created_at')
            ->take(10)
            ->get();
        $pendingWebhooks  = WebhookOutEvent::where('status', 'pending')->count();

        return view('admin.dashboard', compact(
            'totalStudents',
            'activeStudents',
            'totalProjects',
            'completedProjects',
            'recentLogins',
            'pendingWebhooks'
        ));
    }
}
