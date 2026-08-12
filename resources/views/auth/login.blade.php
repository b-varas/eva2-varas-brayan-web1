@extends('layouts.app')
@section('contenido')

{{-- Formulario de inicio de sesión --}}
<h1>Iniciar sesión</h1>

@if ($errors->any())
    <div class="popup popup-error">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('login.store') }}" method="POST">
    @csrf

    <label>Correo:</label><br>
    <input type="email" name="email" value="{{ old('email') }}"><br><br>

    <label>Contraseña:</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Iniciar sesión</button>
</form>

<p>¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>

@endsection