<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório do Projeto - EletroDIM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            background: #ffffff;
        }
        /* ── Cabeçalho ── */
        .header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #F2C300;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-logo {
            display: table-cell;
            width: 40%;
            vertical-align: middle;
        }
        .header-logo .brand {
            font-size: 22px;
            font-weight: 700;
            color: #F2C300;
            letter-spacing: 1px;
        }
        .header-logo .brand span {
            color: #1a1a1a;
        }
        .header-logo .tagline {
            font-size: 9px;
            color: #555555;
            margin-top: 2px;
        }
        .header-meta {
            display: table-cell;
            width: 60%;
            vertical-align: middle;
            text-align: right;
            font-size: 9px;
            color: #555555;
            line-height: 1.5;
        }
        .header-meta strong {
            color: #1a1a1a;
        }
        /* ── Seções ── */
        .section {
            margin-bottom: 14px;
        }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            background-color: #1a1a1a;
            padding: 4px 8px;
            margin-bottom: 6px;
            border-left: 4px solid #F2C300;
        }
        /* ── Grid de dados ── */
        .data-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .data-row {
            display: table-row;
        }
        .data-label {
            display: table-cell;
            width: 18%;
            font-weight: 700;
            color: #555555;
            padding: 3px 6px;
            vertical-align: top;
        }
        .data-value {
            display: table-cell;
            width: 32%;
            color: #1a1a1a;
            padding: 3px 6px;
            vertical-align: top;
            border-bottom: 1px dotted #dddddd;
        }
        /* ── Tabelas ── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        table thead tr th {
            background-color: #F2C300;
            color: #1a1a1a;
            font-weight: 700;
            padding: 4px 5px;
            border: 1px solid #ccaa00;
            text-align: center;
        }
        table tbody tr td {
            padding: 3px 5px;
            border: 1px solid #dddddd;
            text-align: center;
            color: #1a1a1a;
        }
        table tbody tr:nth-child(even) td {
            background-color: #fafafa;
        }
        table tbody tr:hover td {
            background-color: #fff8e1;
        }
        table tfoot tr td {
            background-color: #f5f5f5;
            font-weight: 700;
            padding: 4px 5px;
            border: 1px solid #cccccc;
        }
        .text-left {
            text-align: left !important;
        }
        .text-right {
            text-align: right !important;
        }
        /* ── Badges de configuração ── */
        .badge {
            display: inline-block;
            background-color: #F2C300;
            color: #1a1a1a;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 9px;
        }
        /* ── Rodapé ── */
        .footer {
            margin-top: 16px;
            border-top: 2px solid #F2C300;
            padding-top: 6px;
            text-align: center;
            font-size: 9px;
            color: #888888;
        }
        .footer strong {
            color: #1a1a1a;
        }
        /* ── Quebra de página ── */
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

{{-- ══════════════════════════════════════════════════════════
     CABEÇALHO
══════════════════════════════════════════════════════════ --}}
<div class="header">
    <div class="header-logo">
        <div class="brand">Eletro<span>DIM</span></div>
        <div class="tagline">Dimensionamento Elétrico Residencial</div>
    </div>
    <div class="header-meta">
        <strong>Relatório Técnico de Projeto</strong><br>
        Gerado em: {{ now()->format('d/m/Y \à\s H:i') }}<br>
        @if($project->user)
            Responsável: {{ $project->user->name }}
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     SEÇÃO 1 — DADOS DO PROJETO
══════════════════════════════════════════════════════════ --}}
<div class="section">
    <div class="section-title">1. Dados do Projeto</div>
    <div class="data-grid">
        <div class="data-row">
            <span class="data-label">Nome:</span>
            <span class="data-value">{{ $project->name ?? '—' }}</span>
            <span class="data-label">Cliente:</span>
            <span class="data-value">{{ $project->client_name ?? '—' }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Endereço:</span>
            <span class="data-value">{{ $project->address ?? '—' }}</span>
            <span class="data-label">Cidade/UF:</span>
            <span class="data-value">
                {{ $project->city ?? '' }}{{ ($project->city && $project->state) ? ' / ' : '' }}{{ $project->state ?? '' }}
                @if(!$project->city && !$project->state)—@endif
            </span>
        </div>
        <div class="data-row">
            <span class="data-label">Descrição:</span>
            <span class="data-value">{{ $project->observations ?? '—' }}</span>
            <span class="data-label">Status:</span>
            @php
                $statusLabels = ['draft' => 'Rascunho', 'in_progress' => 'Em andamento', 'completed' => 'Concluído'];
            @endphp
            <span class="data-value">{{ $statusLabels[$project->status] ?? ucfirst($project->status ?? '—') }}</span>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════
     SEÇÃO 2 — CONFIGURAÇÕES DA INSTALAÇÃO
══════════════════════════════════════════════════════════ --}}
<div class="section">
    <div class="section-title">2. Configurações da Instalação</div>
    @php
        $settings = $project->settings;
    @endphp
    @if($settings)
        <div class="data-grid">
            <div class="data-row">
                <span class="data-label">Tensão:</span>
                <span class="data-value">
                    <span class="badge">{{ $settings->voltage ?? '—' }} V</span>
                </span>
                <span class="data-label">Fases:</span>
                <span class="data-value">
                    <span class="badge">{{ $settings->phases ?? '—' }}</span>
                </span>
            </div>
            <div class="data-row">
                <span class="data-label">Método de instalação:</span>
                <span class="data-value">{{ $settings->installation_method ?? '—' }}</span>
                <span class="data-label">Temperatura (°C):</span>
                <span class="data-value">{{ $settings->temperature ?? '—' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label">Padrão da concessionária:</span>
                <span class="data-value">{{ $settings->utility_standard ?? '—' }}</span>
                <span class="data-label">Fator de demanda:</span>
                <span class="data-value">{{ $settings->demand_factor ?? '—' }}</span>
            </div>
        </div>
    @else
        <p style="color:#888; padding:4px 8px; font-style:italic;">Nenhuma configuração registrada.</p>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     SEÇÃO 3 — TABELA DE CIRCUITOS
══════════════════════════════════════════════════════════ --}}
<div class="section">
    <div class="section-title">3. Tabela de Circuitos</div>
    @php
        $circuits = $project->circuitCalculations ?? collect();
        $totalPowerW  = 0;
        $totalPowerVA = 0;
        $totalDemandVA = 0;
    @endphp

    @if($circuits && $circuits->count())
        <table>
            <thead>
                <tr>
                    <th style="width:3%">Nº</th>
                    <th style="width:18%" class="text-left">Descrição</th>
                    <th style="width:7%">Tipo</th>
                    <th style="width:7%">Potência W</th>
                    <th style="width:7%">Potência VA</th>
                    <th style="width:5%">FD</th>
                    <th style="width:8%">Demanda VA</th>
                    <th style="width:5%">FP</th>
                    <th style="width:6%">Tensão V</th>
                    <th style="width:7%">Corrente A</th>
                    <th style="width:7%">Cabo mm²</th>
                    <th style="width:7%">Disjuntor A</th>
                </tr>
            </thead>
            <tbody>
                @foreach($circuits as $index => $circuit)
                    @php
                        $powerW        = (float)($circuit->power_w ?? 0);
                        $powerVA       = (float)($circuit->power_va ?? 0);
                        $demandFactor  = $circuit->use_manual_demand_factor ? $circuit->demand_factor_manual : $circuit->demand_factor_calculated;
                        $demandVA      = (float)($circuit->use_manual_demand_va ? $circuit->demand_va_manual : $circuit->demand_va_calculated);
                        $currentA      = (float)($circuit->project_current_a ?? 0);
                        $conductorMm2  = $circuit->use_manual_final_conductor ? $circuit->final_conductor_manual_mm2 : $circuit->final_conductor_calculated_mm2;
                        $breakerA      = $circuit->use_manual_breaker ? $circuit->breaker_manual_a : $circuit->breaker_calculated_a;
                        $totalPowerW   += $powerW;
                        $totalPowerVA  += $powerVA;
                        $totalDemandVA += $demandVA;
                    @endphp
                    <tr>
                        <td>{{ $circuit->circuit_number ?? $index + 1 }}</td>
                        <td class="text-left">{{ $circuit->description ?? '—' }}</td>
                        <td>{{ $circuit->circuit_type ?? '—' }}</td>
                        <td>{{ number_format($powerW, 0, ',', '.') }}</td>
                        <td>{{ number_format($powerVA, 0, ',', '.') }}</td>
                        <td>{{ isset($demandFactor) ? number_format((float)$demandFactor, 2, ',', '') : '—' }}</td>
                        <td>{{ number_format($demandVA, 0, ',', '.') }}</td>
                        <td>{{ isset($circuit->power_factor) ? number_format((float)$circuit->power_factor, 2, ',', '') : '—' }}</td>
                        <td>{{ $circuit->voltage ?? '—' }}</td>
                        <td>{{ number_format($currentA, 2, ',', '') }}</td>
                        <td>{{ isset($conductorMm2) ? number_format((float)$conductorMm2, 2, ',', '') : '—' }}</td>
                        <td>{{ $breakerA ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-left">TOTAIS</td>
                    <td>{{ number_format($totalPowerW, 0, ',', '.') }}</td>
                    <td>{{ number_format($totalPowerVA, 0, ',', '.') }}</td>
                    <td>—</td>
                    <td>{{ number_format($totalDemandVA, 0, ',', '.') }}</td>
                    <td colspan="5">—</td>
                </tr>
            </tfoot>
        </table>
    @else
        <p style="color:#888; padding:4px 8px; font-style:italic;">Nenhum circuito calculado para este projeto.</p>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════
     RODAPÉ
══════════════════════════════════════════════════════════ --}}
<div class="footer">
    Gerado pelo <strong>EletroDIM</strong> em {{ now()->format('d/m/Y \à\s H:i') }} &nbsp;|&nbsp; <strong>Prof. Emerson Leite</strong>
</div>

</body>
</html>
