@extends('layouts.admin')
@section('title', 'Alunos')
@section('page-title', 'Gerenciar Alunos')
@section('page-subtitle', 'Cadastre, ative, bloqueie e gerencie os acessos')
@section('content')
    @livewire('admin.user-list')
@endsection
