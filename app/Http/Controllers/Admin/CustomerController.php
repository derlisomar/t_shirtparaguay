<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = User::where('is_admin', false)->withCount('orders')
            ->withSum(['orders as spent' => fn ($o) => $o->where('status', '!=', 'cancelado')], 'total')
            ->latest();
        if ($request->input('tipo') === 'mayorista') {
            $q->where('is_wholesale', true);
        }
        if ($t = $request->input('q')) {
            $q->where(fn ($w) => $w->where('name', 'ilike', "%$t%")->orWhere('last_name', 'ilike', "%$t%")->orWhere('username', 'ilike', "%$t%")->orWhere('email', 'ilike', "%$t%")
                ->orWhere('phone', 'ilike', "%$t%")->orWhere('document', 'ilike', "%$t%"));
        }

        return view('admin.customers.index', ['customers' => $q->paginate(25)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.customers.show', ['customer' => new User]);
    }

    public function store(Request $request)
    {
        $customer = new User(['is_admin' => false]);
        $this->save($request, $customer);

        return redirect()->route('admin.customers.show', $customer)->with('ok', 'Cliente creado.');
    }

    public function show(User $user)
    {
        abort_if($user->is_admin, 404);

        return view('admin.customers.show', ['customer' => $user->load(['orders' => fn ($q) => $q->latest()])]);
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->is_admin, 404);
        $this->save($request, $user);

        return back()->with('ok', 'Datos del cliente guardados.');
    }

    public function destroy(User $user)
    {
        abort_if($user->is_admin, 404);
        $name = $user->fullName();
        // Sus pedidos se conservan (quedan con el nombre y los datos que tenían) pero sin cuenta asociada
        $user->delete();

        return redirect()->route('admin.customers.index')->with('ok', "Se eliminó a {$name}. Sus pedidos se conservan.");
    }

    private function save(Request $request, User $user): void
    {
        \App\Support\Accounts::normalize($request);
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'username' => ['nullable', 'string', 'min:3', 'max:40', 'regex:/^[\pL\pN._-]+$/u', Rule::unique('users', 'username')->ignore($user->id)],
            'country' => ['nullable', Rule::in(\App\Support\Accounts::COUNTRIES)],
            'email' => ['nullable', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:40',
            'document' => 'nullable|string|max:30',
            'city' => 'nullable|string|max:80',
            'address' => 'nullable|string|max:255',
            'purchase_limit' => 'nullable|string|max:20',
            'admin_notes' => 'nullable|string|max:2000',
            'password' => 'nullable|string|min:6|max:100',
        ], [
            'email.unique' => 'Ya hay una cuenta con ese correo.',
            'username.unique' => 'Ese nombre de usuario ya lo tiene otro cliente.',
            'username.regex' => 'El usuario puede tener letras, números, punto, guion o guion bajo, sin espacios.',
            'password.min' => 'La contraseña tiene que tener al menos 6 caracteres.',
        ]);

        $limit = (int) preg_replace('/\D/', '', (string) ($data['purchase_limit'] ?? ''));
        $user->fill([
            'name' => $data['name'],
            'last_name' => $data['last_name'] ?? null,
            'username' => ! empty($data['username']) ? mb_strtolower($data['username']) : null,
            'country' => $data['country'] ?? null,
            'email' => $data['email'] ? mb_strtolower($data['email']) : null,
            'phone' => $data['phone'] ?? null,
            'document' => $data['document'] ?? null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'purchase_limit' => $limit > 0 ? $limit : null,
            'admin_notes' => $data['admin_notes'] ?? null,
            'is_wholesale' => $request->boolean('is_wholesale'),
        ]);
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        } elseif (! $user->exists) {
            // Cliente cargado sin contraseña: puede tener una más adelante si se la ponés desde acá
            $user->password = Str::random(32);
        }
        $user->is_admin = false;
        $user->save();
    }
}
