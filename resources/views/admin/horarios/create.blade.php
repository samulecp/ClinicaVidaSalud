
@extends('layouts.admin')

@section('title', 'Registrar horario')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Registrar horario médico</h1>
    <p class="text-gray-600 mb-6">Define el día y el intervalo de atención.</p>

    <div class="bg-white rounded-xl shadow p-6">
        <form method="POST" action="{{ route('admin.horarios.store') }}">
            @include('admin.horarios._form')
        </form>
    </div>
</div>
@endsection
