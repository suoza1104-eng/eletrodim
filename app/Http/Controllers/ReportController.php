<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectReport;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct(protected PdfService $pdfService) {}

    /**
     * Exibe o relatório público via token de compartilhamento.
     */
    public function showShare(string $token)
    {
        $report = ProjectReport::where('share_token', $token)->firstOrFail();

        $project = $report->project()->with([
            'user',
            'circuits',
            'circuits.loads',
            'installationBoard',
        ])->firstOrFail();

        return view('reports.share', compact('project', 'report'));
    }

    /**
     * Gera e faz download do PDF do projeto.
     * Apenas o dono do projeto ou um admin pode acessar.
     */
    public function downloadPdf(int $projectId)
    {
        $user    = Auth::user();
        $project = Project::findOrFail($projectId);

        // Autorização: dono do projeto ou admin
        if ($user->role !== 'admin' && $project->user_id !== $user->id) {
            abort(403);
        }

        return $this->pdfService->download($project);
    }
}
