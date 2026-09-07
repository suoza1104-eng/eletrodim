<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Exibe o painel do aluno com resumo dos seus projetos.
     */
    public function showDashboard()
    {
        $user = Auth::user();

        $totalProjects     = $user->projects()->count();
        $completedProjects = $user->projects()->where('status', 'completed')->count();
        $inProgressProjects = $user->projects()->where('status', 'in_progress')->count();
        $recentProjects    = $user->projects()->latest()->take(5)->get();

        return view('student.dashboard', compact(
            'user',
            'totalProjects',
            'completedProjects',
            'inProgressProjects',
            'recentProjects'
        ));
    }
}
