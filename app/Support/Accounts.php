<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Cuentas de clientes de la tienda: se registran con nombre de usuario (el correo es opcional)
 * y entran con usuario, documento o correo + contraseña.
 */
class Accounts
{
    const COUNTRIES = ['Paraguay', 'Argentina', 'Brasil', 'Bolivia', 'Uruguay', 'Chile', 'Otro'];

    /** Reglas del formulario de registro (también se usan al finalizar la compra sin cuenta) */
    public static function rules(?User $ignore = null, bool $withPassword = true): array
    {
        return [
            'first_name' => 'required|string|max:80',
            'last_name' => 'required|string|max:120',
            'document' => 'required|string|max:30',
            'phone' => 'required|string|max:40',
            'country' => ['required', Rule::in(self::COUNTRIES)],
            'city' => 'required|string|max:80',
            'address' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:160', Rule::unique('users', 'email')->ignore($ignore?->id)],
            'username' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[\pL\pN._-]+$/u', Rule::unique('users', 'username')->ignore($ignore?->id)],
        ] + ($withPassword ? ['password' => 'required|string|min:6|max:100|confirmed'] : []);
    }

    public static function messages(): array
    {
        return [
            'first_name.required' => 'Poné tu nombre.',
            'last_name.required' => 'Poné tu apellido.',
            'document.required' => 'Poné tu número de cédula o RUC.',
            'phone.required' => 'Poné tu número de celular.',
            'city.required' => 'Poné tu ciudad.',
            'address.required' => 'Poné tu dirección.',
            'email.email' => 'Ese correo no parece válido. Si no tenés, dejalo vacío.',
            'email.unique' => 'Ya hay una cuenta con ese correo. Probá ingresar.',
            'username.required' => 'Elegí un nombre de usuario para entrar después.',
            'username.min' => 'El usuario tiene que tener al menos 3 letras o números.',
            'username.regex' => 'El usuario puede tener letras, números, punto, guion o guion bajo, sin espacios.',
            'username.unique' => 'Ese nombre de usuario ya está usado. Probá con otro (por ejemplo, agregale tu año de nacimiento).',
            'password.required' => 'Elegí una contraseña.',
            'password.min' => 'La contraseña tiene que tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }

    /** Antes de validar: usuario y correo en minúsculas, así «Carlos» y «carlos» son el mismo usuario */
    public static function normalize(Request $request): void
    {
        $request->merge(array_filter([
            'username' => is_string($request->input('username')) ? mb_strtolower(trim($request->input('username'))) : null,
            'email' => is_string($request->input('email')) ? mb_strtolower(trim($request->input('email'))) : null,
        ], fn ($v) => $v !== null));
    }

    /** Deja los datos listos para guardar en la tabla users */
    public static function attributes(array $data): array
    {
        $out = [
            'name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'document' => trim($data['document']),
            'phone' => trim($data['phone']),
            'country' => $data['country'],
            'city' => trim($data['city']),
            'address' => trim($data['address']),
            'email' => ! empty($data['email']) ? mb_strtolower(trim($data['email'])) : null,
            'username' => mb_strtolower(trim($data['username'])),
        ];
        if (! empty($data['password'])) {
            $out['password'] = $data['password'];
        }

        return $out;
    }

    /** Crea la cuenta del cliente y lo deja adentro, sin perder el carrito */
    public static function register(Request $request, array $data): User
    {
        $cart = session('cart', []);
        $user = User::create(self::attributes($data) + ['is_admin' => false]);
        Auth::login($user, true);
        $request->session()->regenerate();
        session(['cart' => $cart]);

        return $user;
    }

    /** Busca al cliente por usuario, correo o documento (el documento solo si es de una sola cuenta) */
    public static function findForLogin(string $login): ?User
    {
        $login = mb_strtolower(trim($login));
        if ($login === '') {
            return null;
        }
        $user = User::where('is_admin', false)
            ->where(fn ($q) => $q->whereRaw('lower(username) = ?', [$login])->orWhereRaw('lower(email) = ?', [$login]))
            ->first();
        if ($user) {
            return $user;
        }
        $byDoc = User::where('is_admin', false)->where('document', $login)->take(2)->get();

        return $byDoc->count() === 1 ? $byDoc->first() : null;
    }
}
