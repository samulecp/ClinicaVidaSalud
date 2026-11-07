
@extends('layouts.admin')

@section('title', 'Registrar especialidad')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Registrar especialidad</h2>
        <p class="mt-1 text-sm text-gray-600">Añade una nueva especialidad médica.</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.especialidades.store') }}">
            @csrf
            @include('admin.especialidades._form', ['boton' => 'Registrar especialidad'])
        </form>
    </div>
</div>
@endsection
