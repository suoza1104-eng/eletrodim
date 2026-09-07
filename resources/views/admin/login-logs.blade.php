@extends('layouts.admin')
@section('title', 'Logs de Acesso')
@section('page-title', 'Logs de Acesso')
@section('page-subtitle', 'Histórico de logins e tentativas de acesso ao sistema')
@section('content')
    @livewire('admin.login-logs')
@endsection
