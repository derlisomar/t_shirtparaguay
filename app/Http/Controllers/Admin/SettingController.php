<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public const FIELDS = [
        'store_name', 'store_tagline', 'currency', 'whatsapp', 'instagram', 'facebook', 'tiktok', 'email', 'address',
        'shipping_cost', 'free_shipping_from', 'low_stock', 'bank_details', 'announcement', 'pickup_address', 'slider_seconds', 'show_free_shipping',
        'story_show', 'story_title', 'story_text', 'story_button', 'story_link',
    ];

    public function edit()
    {
        return view('admin.settings', ['s' => Setting::allCached()]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:60',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'logo_dark' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'favicon' => 'nullable|image|mimes:png,svg,ico|max:512',
            'story_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
            'story_title' => 'nullable|string|max:120',
            'story_text' => 'nullable|string|max:600',
            'story_button' => 'nullable|string|max:40',
            'story_link' => 'nullable|string|max:255',
        ], [
            'story_image.max' => 'La foto del bloque del inicio puede pesar hasta 6 MB.',
        ]);
        $values = [];
        foreach (self::FIELDS as $f) {
            $v = $request->input($f);
            if (in_array($f, ['shipping_cost', 'free_shipping_from', 'low_stock'])) {
                $v = (string) (int) preg_replace('/\D/', '', (string) $v);
            }
            if ($f === 'show_free_shipping' || $f === 'story_show') {
                $v = $request->boolean($f) ? '1' : '0';
            }
            if (in_array($f, ['story_text', 'story_button'])) {
                $v = (string) $v; // vacío a propósito = sin texto / sin botón
            }
            if ($f === 'slider_seconds') {
                $v = (string) max(2, min(20, (int) $v ?: 4));
            }
            $values[$f] = $v;
        }
        foreach (['logo', 'logo_dark', 'favicon', 'story_image'] as $f) {
            if ($request->hasFile($f)) {
                Uploads::delete(setting($f));
                $values[$f] = Uploads::store($request->file($f), $f === 'story_image' ? 'home' : 'brand');
            } elseif ($request->boolean("remove_$f")) {
                Uploads::delete(setting($f));
                $values[$f] = null;
            }
        }
        Setting::put($values);

        return back()->with('ok', 'Configuración guardada.');
    }

    public function admins()
    {
        return view('admin.admins', ['admins' => User::where('is_admin', true)->orderBy('name')->get()]);
    }

    public function storeAdmin(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'password' => ['required', Password::min(8)],
        ]);
        $user = User::firstOrNew(['email' => $data['email']]);
        $user->fill($data + ['is_admin' => true])->save();

        return back()->with('ok', "{$user->name} ya puede entrar al panel.");
    }

    public function destroyAdmin(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['admin' => 'No podés quitarte el acceso a vos mismo.']);
        }
        $user->update(['is_admin' => false]);

        return back()->with('ok', "{$user->name} ya no tiene acceso al panel.");
    }
}
