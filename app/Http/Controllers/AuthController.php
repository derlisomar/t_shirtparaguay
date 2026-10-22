<?php

namespace App\Http\Controllers;

use App\Support\Accounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $request->validate(['login' => 'required|string|max:160', 'password' => 'required'], [
            'login.required' => 'Poné tu usuario.',
            'password.required' => 'Poné tu contraseña.',
        ]);
        $cart = session('cart', []);

        // Se puede entrar con el usuario, el número de documento o el correo
        $user = Accounts::findForLogin($request->input('login'));
        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['login' => 'El usuario o la contraseña no coinciden.'])->onlyInput('login');
        }
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        session(['cart' => $cart]); // el carrito se conserva al ingresar

        return redirect()->intended(route('account'));
    }

    public function register(Request $request)
    {
        Accounts::normalize($request);
        $data = $request->validate(Accounts::rules(), Accounts::messages());
        Accounts::register($request, $data);

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
            return redirect()->route('admin.reports');
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

        return redirect()->intended(route('admin.reports'));
    }
}
