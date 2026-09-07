@extends('layouts.admin')
@section('title', 'Painel Admin')
@section('page-title', 'Painel Administrativo')
@section('page-subtitle', 'Gestão de alunos e monitoramento do sistema')
@section('content')

<!-- Cards de métricas admin -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500 mb-2">Total de Alunos</p>
        <p class="text-3xl font-black text-gray-900">{{ $totalStudents }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500 mb-2">Alunos Ativos</p>
        <p class="text-3xl font-black text-green-600">{{ $activeStudents }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500 mb-2">Total de Projetos</p>
        <p class="text-3xl font-black text-gray-900">{{ $totalProjects }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <p class="text-sm text-gray-500 mb-2">Webhooks Pendentes</p>
        <p class="text-3xl font-black {{ $pendingWebhooks > 0 ? 'text-orange-500' : 'text-gray-900' }}">{{ $pendingWebhooks }}</p>
    </div>
</div>

<!-- Logins recentes -->
<div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800">Acessos Recentes</h3>
    </div>
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-50">
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Usuário</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">IP</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Data/Hora</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($recentLogins as $log)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-sm text-gray-800">{{ $log->user?->name ?? $log->email }}</td>
                <td class="px-6 py-3 text-sm text-gray-500">{{ $log->ip_address }}</td>
                <td class="px-6 py-3 text-sm text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400 text-sm">Nenhum acesso registrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
