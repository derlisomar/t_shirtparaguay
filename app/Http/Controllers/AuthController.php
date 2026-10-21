<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('shop.auth.login');
    }

    public function showRegister()
    {
        return view('shop.auth.register');
    }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => 'required|email', 'password' => 'required']);
        $cart = session('cart', []);

        if (! Auth::attempt($cred, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'El correo o la contraseña no coinciden.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        session(['cart' => $cart]); // el carrito se conserva al ingresar

        return redirect()->intended(route('account'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'phone' => 'nullable|string|max:40',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Ya hay una cuenta con ese correo. Probá ingresar.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña tiene que tener al menos 8 caracteres.',
        ]);
        $cart = session('cart', []);

        $user = User::create($data + ['is_admin' => false]);
        Auth::login($user, true);
        $request->session()->regenerate();
        session(['cart' => $cart]);

        return redirect()->intended(route('account'))->with('ok', '¡Bienvenido/a a '.setting('store_name', 't_shirtparaguay').'!');
    }

    public function logout(Request $request)
    {
        $wasAdmin = $request->user()?->is_admin;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($wasAdmin && str_contains(url()->previous(), '/admin') ? route('admin.login') : route('home'));
    }

    public function showAdminLogin()
    {
        if (auth()->user()?->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function adminLogin(Request $request)
    {
        $cred = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($cred + ['is_admin' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Datos incorrectos o la cuenta no es de administrador.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }
}
