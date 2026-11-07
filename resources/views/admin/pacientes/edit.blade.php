@extends(auth()->user()->rol === 'recepcionista' ? 'layouts.recepcion' : 'layouts.admin')
@php
    $rutaPacientes = auth()->user()->rol === 'recepcionista'
        ? 'recepcion.pacientes'
        : 'admin.pacientes';
@endphp
@section('title', 'Editar paciente')

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Editar paciente</h2>
        <p class="text-gray-500 mt-1">
            Modifica los datos de {{ $paciente->nombres }} {{ $paciente->apellidos }}.
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('admin.pacientes.update', $paciente) }}" method="POST">
            @csrf
            @method('PUT')

            @include('admin.pacientes._form', [
                'paciente' => $paciente,
                'boton' => 'Guardar cambios'
            ])
        </form>
    </div>

</div>
@endsection