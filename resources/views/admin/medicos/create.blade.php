
@extends('layouts.admin')

@section('title', 'Registrar médico')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Registrar médico</h2>
        <p class="mt-1 text-sm text-gray-600">
            Introduce los datos profesionales y las especialidades del médico.
        </p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('admin.medicos.store') }}" method="POST">
            @csrf
            @include('admin.medicos._form', ['boton' => 'Registrar médico'])
        </form>
    </div>
</div>
@endsection
