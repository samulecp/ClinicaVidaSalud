@extends(auth()->user()->rol === 'recepcionista' ? 'layouts.recepcion' : 'layouts.admin')
@php
    $rutaPacientes = auth()->user()->rol === 'recepcionista'
        ? 'recepcion.pacientes'
        : 'admin.pacientes';
@endphp
@section('title', 'Registrar paciente')

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Registrar paciente</h2>
        <p class="text-gray-500 mt-1">Introduce los datos personales del nuevo paciente.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form action="{{ route('admin.pacientes.store') }}" method="POST">
            @csrf

            @include('admin.pacientes._form', [
                'boton' => 'Registrar paciente'
            ])
        </form>
    </div>

</div>
@endsection