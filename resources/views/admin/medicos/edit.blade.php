
@extends('layouts.admin')

@section('title', 'Editar médico')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Editar médico</h2>
        <p class="mt-1 text-sm text-gray-600">
            Modifica los datos de {{ $medico->nombres }} {{ $medico->apellidos }}.
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.medicos.update', $medico) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.medicos._form', ['boton' => 'Guardar cambios'])
        </form>
    </div>
</div>
@endsection
