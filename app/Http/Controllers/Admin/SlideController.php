<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Slide;
use App\Support\Uploads;
use Illuminate\Http\Request;

class SlideController extends Controller
{
    public function index()
    {
        return view('admin.slides.index', ['slides' => Slide::with('product')->orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create()
    {
        return $this->form(new Slide(['is_active' => true, 'bg_color' => '#2b211c', 'accent_color' => '#ff7a2f', 'button_text' => 'Ver más']));
    }

    public function edit(Slide $slide)
    {
        return $this->form($slide);
    }

    private function form(Slide $slide)
    {
        return view('admin.slides.form', [
            'slide' => $slide,
            'products' => Product::orderBy('name')->get(['id', 'name', 'price']),
        ]);
    }

    public function store(Request $request)
    {
        $slide = new Slide(['sort_order' => (int) Slide::max('sort_order') + 1]);
        $this->save($request, $slide);

        return redirect()->route('admin.slides.index')->with('ok', 'Slide creado.');
    }

    public function update(Request $request, Slide $slide)
    {
        $this->save($request, $slide);

        return redirect()->route('admin.slides.edit', $slide)->with('ok', 'Slide guardado.');
    }

    private function save(Request $request, Slide $slide): void
    {
        $img = 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144';
        $data = $request->validate([
            'title' => 'required|string|max:80',
            'text' => 'nullable|string|max:400',
            'tagline' => 'nullable|string|max:80',
            'button_text' => 'nullable|string|max:40',
            'button_link' => 'nullable|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'bg_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'main_image' => $img,
            'secondary_image' => $img,
            'thumb_image' => $img,
        ]);
        $slide->fill(collect($data)->except(['main_image', 'secondary_image', 'thumb_image'])->all());
        $slide->is_active = $request->boolean('is_active');

        foreach (['main_image', 'secondary_image', 'thumb_image'] as $field) {
            if ($request->hasFile($field)) {
                Uploads::delete($slide->$field);
                $slide->$field = Uploads::store($request->file($field), 'slides');
            } elseif ($request->boolean("remove_$field")) {
                Uploads::delete($slide->$field);
                $slide->$field = null;
            }
        }
        $slide->save();
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('order', []) as $i => $id) {
            Slide::whereKey($id)->update(['sort_order' => $i]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Slide $slide)
    {
        foreach (['main_image', 'secondary_image', 'thumb_image'] as $f) {
            Uploads::delete($slide->$f);
        }
        $slide->delete();

        return back()->with('ok', 'Slide borrado.');
    }
}
