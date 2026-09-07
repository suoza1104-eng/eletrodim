@extends('layouts.auth')
@section('title', 'Entrar')
@section('content')
<div class="bg-white rounded-2xl shadow-2xl p-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Bem-vindo de volta</h2>
    <p class="text-gray-500 text-sm mb-8">Faça login para continuar no EletroDIM</p>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-red-700 text-sm">{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}" class="space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400
                          focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition"
                   placeholder="seu@email.com">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Senha</label>
            <input type="password" name="password" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400
                          focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition"
                   placeholder="••••••••">
        </div>
        <button type="submit"
                class="w-full bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold py-3 px-6
                       rounded-xl transition-colors duration-200 text-sm tracking-wide">
            Entrar no EletroDIM
        </button>
    </form>

    <p class="text-center mt-6 text-sm text-gray-500">
        Esqueceu sua senha?
        <span class="text-brand-yellow font-medium cursor-pointer">Fale com o suporte</span>
    </p>
</div>
@endsection
