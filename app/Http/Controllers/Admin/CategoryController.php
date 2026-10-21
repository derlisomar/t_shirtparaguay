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
            'categories' => Category::withCount('products')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category(['is_active' => true])]);
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
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

    private function save(Request $request, Category $category): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
        ]);
        $category->fill([
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
        Uploads::delete($category->image);
        $category->delete();

        return back()->with('ok', 'Categoría borrada.');
    }
}
