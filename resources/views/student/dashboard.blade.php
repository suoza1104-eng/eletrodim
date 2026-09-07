@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Olá, ' . Auth::user()->name . '!')
@section('page-subtitle', 'Aqui está o resumo dos seus projetos')
@section('content')

<!-- Cards de métricas -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card Total de Projetos -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors duration-200">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Projetos</span>
            <div class="w-10 h-10 bg-yellow-50 dark:bg-yellow-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-brand-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $totalProjects }}</p>
    </div>
    <!-- Card Concluídos -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors duration-200">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Concluídos</span>
            <div class="w-10 h-10 bg-green-50 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $completedProjects }}</p>
    </div>
    <!-- Card Em andamento -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors duration-200">
        <div class="flex items-center justify-between mb-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Em Andamento</span>
            <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $inProgressProjects }}</p>
    </div>
</div>

<!-- Botão Novo Projeto -->
<div class="flex items-center justify-between mb-6">
    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Projetos Recentes</h3>
    <a href="{{ route('student.project.new') }}"
       class="bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold px-5 py-2.5 rounded-xl text-sm transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Novo Projeto
    </a>
</div>

<!-- Lista projetos recentes -->
@if($recentProjects->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-12 text-center transition-colors duration-200">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <p class="text-gray-600 dark:text-gray-300 font-medium mb-2">Nenhum projeto ainda</p>
        <p class="text-gray-400 dark:text-gray-500 text-sm mb-6">Crie um novo projeto e deixe o EletroDIM organizar os cálculos para você.</p>
        <a href="{{ route('student.project.new') }}"
           class="bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold px-6 py-2.5 rounded-xl text-sm transition-colors inline-block">
            Criar primeiro projeto
        </a>
    </div>
@else
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors duration-200">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-700">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Projeto</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cliente</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progresso</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                @foreach($recentProjects as $project)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ $project->name }}</p>
                        <p class="text-gray-400 dark:text-gray-500 text-xs mt-0.5">{{ $project->created_at->format('d/m/Y') }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $project->client_name ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @php
                            $statusColors = ['draft' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', 'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300', 'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'];
                            $statusLabels = ['draft' => 'Rascunho', 'in_progress' => 'Em andamento', 'completed' => 'Concluído'];
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$project->status] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                            {{ $statusLabels[$project->status] ?? $project->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 w-24">
                                <div class="bg-brand-yellow h-1.5 rounded-full" style="width: {{ $project->progress_percent }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $project->progress_percent }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('student.project.edit', $project->id) }}"
                           class="text-brand-yellow hover:text-brand-yellow-hover font-medium text-sm transition-colors">
                            Continuar →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4 text-center">
        <a href="{{ route('student.projects') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-brand-yellow transition-colors">
            Ver todos os projetos →
        </a>
    </div>
@endif
@endsection
