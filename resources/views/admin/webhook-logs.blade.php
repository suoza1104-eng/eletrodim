@extends('layouts.admin')
@section('title', 'Logs de Webhook')
@section('page-title', 'Logs de Webhook')
@section('page-subtitle', 'Registro de eventos recebidos e processados via webhook')
@section('content')
    @livewire('admin.webhook-logs')
@endsection
