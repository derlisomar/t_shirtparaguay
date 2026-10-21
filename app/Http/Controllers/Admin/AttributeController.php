<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Http\Request;

/** Talles y colores que se usan al cargar productos. */
class AttributeController extends Controller
{
    public function index()
    {
        return view('admin.attributes', [
            'sizes' => Size::withCount('variants')->orderBy('sort_order')->get(),
            'colors' => Color::withCount('variants')->orderBy('sort_order')->get(),
        ]);
    }

    public function storeSize(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:30|unique:sizes,name', 'sort_order' => 'nullable|integer']);
        Size::create($data + ['sort_order' => (int) Size::max('sort_order') + 1]);

        return back()->with('ok', 'Talle agregado.');
    }

    public function updateSize(Request $request, Size $size)
    {
        $data = $request->validate(['name' => 'required|string|max:30|unique:sizes,name,'.$size->id, 'sort_order' => 'nullable|integer']);
        $size->update($data);

        return back()->with('ok', 'Talle guardado.');
    }

    public function destroySize(Size $size)
    {
        if ($size->variants()->exists()) {
            return back()->withErrors(['size' => "El talle {$size->name} está en uso en productos."]);
        }
        $size->delete();

        return back()->with('ok', 'Talle borrado.');
    }

    public function storeColor(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:60|unique:colors,name', 'hex' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/']]);
        Color::create($data + ['sort_order' => (int) Color::max('sort_order') + 1]);

        return back()->with('ok', 'Color agregado.');
    }

    public function updateColor(Request $request, Color $color)
    {
        $data = $request->validate([
            'name' => 'required|string|max:60|unique:colors,name,'.$color->id,
            'hex' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order' => 'nullable|integer',
        ]);
        $color->update($data);

        return back()->with('ok', 'Color guardado.');
    }

    public function destroyColor(Color $color)
    {
        if ($color->variants()->exists()) {
            return back()->withErrors(['color' => "El color {$color->name} está en uso en productos."]);
        }
        $color->delete();

        return back()->with('ok', 'Color borrado.');
    }
}
