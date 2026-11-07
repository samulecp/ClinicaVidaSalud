
@extends('layouts.admin')

@section('title', 'Editar especialidad')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Editar especialidad</h2>
        <p class="mt-1 text-sm text-gray-600">Modifica la información de {{ $especialidad->nombre }}.</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.especialidades.update', $especialidad) }}">
            @csrf
            @method('PUT')
            @include('admin.especialidades._form', ['boton' => 'Guardar cambios'])
        </form>
    </div>
</div>
@endsection
