<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de registro.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Requerimiento: Registro de usuario, con cifrado de la clave.
     */
    public function register(Request $request)
    {
        $validado = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $usuario = User::create([
            'name' => $validado['name'],
            'email' => $validado['email'],
            'password' => Hash::make($validado['password']),
        ]);

        Auth::login($usuario);

        return redirect()->route('projects.index')->with('success', 'Cuenta creada correctamente. ¡Bienvenido!');
    }

    /**
     * Muestra el formulario de inicio de sesión.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Requerimiento: Inicio de sesión, validando credenciales.
     */
    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credenciales)) {
            $request->session()->regenerate();

            return redirect()->route('projects.index')->with('success', 'Sesión iniciada correctamente.');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con ningún registro.',
        ])->onlyInput('email');
    }

    /**
     * Cierra la sesión del usuario actual.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}