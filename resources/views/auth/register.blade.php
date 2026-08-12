@extends('layouts.app')
@section('contenido')

{{-- Formulario de registro de usuario --}}
<h1>Crear cuenta</h1>

@if ($errors->any())
    <div class="popup popup-error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('register.store') }}" method="POST">
    @csrf

    <label>Nombre:</label><br>
    <input type="text" name="name" value="{{ old('name') }}"><br><br>

    <label>Correo:</label><br>
    <input type="email" name="email" value="{{ old('email') }}"><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="password"><br><br>

    <label>Confirmar contraseña:</label><br>
    <input type="password" name="password_confirmation"><br><br>

    <button type="submit">Registrarme</button>
</form>

<p>¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a></p>

@endsection