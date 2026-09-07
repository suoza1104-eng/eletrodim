<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectReport;
use Barryvdh\LaravelDompdf\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    // Gera o PDF do projeto e salva em storage/app/pdfs/
    // Retorna o caminho do arquivo gerado
    public function generate(Project $project): string
    {
        $project->load([
            'settings',
            'inputRows',
            'loadRows',
            'circuitCalculations',
            'phaseDistributions',
            'conduits',
            'dps',
            'idr',
            'serviceEntrance',
        ]);

        $pdf = Pdf::loadView('pdf.project-report', compact('project'))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'sans-serif');

        $filename = 'project_' . $project->id . '_' . now()->format('Ymd_His') . '.pdf';
        $path = 'pdfs/' . $filename;

        Storage::put($path, $pdf->output());

        // Registra o relatório
        ProjectReport::create([
            'project_id' => $project->id,
            'report_type' => 'pdf',
            'file_path' => $path,
            'generated_at' => now(),
            'created_at' => now(),
        ]);

        return $path;
    }

    // Gera e retorna o PDF como response para download
    public function download(Project $project): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $path = $this->generate($project);
        $filename = 'EletroDIM_Projeto_' . Str::slug($project->name) . '.pdf';
        return Storage::download($path, $filename);
    }

    // Gera link de compartilhamento público
    public function generateShareLink(Project $project): string
    {
        $token = Str::uuid()->toString();

        ProjectReport::create([
            'project_id' => $project->id,
            'report_type' => 'share',
            'share_token' => $token,
            'generated_at' => now(),
            'created_at' => now(),
        ]);

        $project->update(['share_token' => $token]);

        return route('report.share', ['token' => $token]);
    }
}
