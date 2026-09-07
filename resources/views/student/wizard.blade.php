@extends('layouts.app')
@section('title', 'Projeto')
@section('page-title', $projectId ? 'Editar Projeto' : 'Novo Projeto')
@section('page-subtitle', 'Wizard de dimensionamento elétrico')
@section('content')
    @livewire('student.wizard.project-wizard', ['projectId' => $projectId])
@endsection
