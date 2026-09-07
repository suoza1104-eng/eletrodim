@extends('layouts.admin')
@section('title', 'Painel Admin')
@section('page-title', 'Painel Administrativo')
@section('page-subtitle', 'Gestão de alunos e monitoramento do sistema')
@section('content')

<!-- Cards de métricas admin -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors duration-200">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Total de Alunos</p>
        <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $totalStudents }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors duration-200">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Alunos Ativos</p>
        <p class="text-3xl font-black text-green-600 dark:text-green-400">{{ $activeStudents }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors duration-200">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Total de Projetos</p>
        <p class="text-3xl font-black text-gray-900 dark:text-gray-100">{{ $totalProjects }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 transition-colors duration-200">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Webhooks Pendentes</p>
        <p class="text-3xl font-black {{ $pendingWebhooks > 0 ? 'text-orange-500 dark:text-orange-400' : 'text-gray-900 dark:text-gray-100' }}">{{ $pendingWebhooks }}</p>
    </div>
</div>

<!-- Logins recentes -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors duration-200">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Acessos Recentes</h3>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-50 dark:border-gray-700">
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Usuário</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">IP</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Data/Hora</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
            @forelse($recentLogins as $log)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-3 text-sm text-gray-800 dark:text-gray-200">{{ $log->user?->name ?? $log->email }}</td>
                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $log->ip_address }}</td>
                <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400 dark:text-gray-500 text-sm">Nenhum acesso registrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
