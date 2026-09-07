@extends('layouts.app')
@section('title', 'Meus Projetos')
@section('page-title', 'Meus Projetos')
@section('page-subtitle', 'Gerencie todos os seus projetos de dimensionamento')
@section('content')
    @livewire('student.project-list')
@endsection
