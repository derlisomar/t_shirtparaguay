<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', [
            'categories' => Category::tree(fn ($q) => $q->withCount('products')),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_active' => true, 'parent_id' => request()->integer('dentro_de') ?: null]),
            'parents' => $this->parents(),
        ]);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', ['category' => $category, 'parents' => $this->parents($category)]);
    }

    public function store(Request $request)
    {
        $this->save($request, new Category);

        return redirect()->route('admin.categories.index')->with('ok', 'Categoría creada.');
    }

    public function update(Request $request, Category $category)
    {
        $this->save($request, $category);

        return redirect()->route('admin.categories.index')->with('ok', 'Categoría guardada.');
    }

    /** Categorías principales donde se puede poner una línea (no la misma ni una que ya tiene líneas) */
    private function parents(?Category $category = null)
    {
        if ($category?->exists && $category->children()->exists()) {
            return collect(); // ya tiene líneas: tiene que seguir siendo principal
        }

        return Category::roots()->when($category?->exists, fn ($q) => $q->whereKeyNot($category->id))
            ->orderBy('sort_order')->orderBy('name')->get();
    }

    private function save(Request $request, Category $category): void
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::in($this->parents($category)->pluck('id'))],
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
        ]);
        $category->fill([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);
        if (! $category->slug) {
            $slug = Str::slug($data['name']);
            $category->slug = Category::where('slug', $slug)->exists() ? $slug.'-'.Str::random(4) : $slug;
        }
        if ($request->hasFile('image')) {
            Uploads::delete($category->image);
            $category->image = Uploads::store($request->file('image'), 'categories');
        }
        $category->save();
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'La categoría tiene productos. Pasalos a otra categoría antes de borrarla.']);
        }
        if ($category->children()->exists()) {
            return back()->withErrors(['category' => 'La categoría tiene líneas adentro. Borralas o movelas antes.']);
        }
        Uploads::delete($category->image);
        $category->delete();

        return back()->with('ok', 'Categoría borrada.');
    }
}
