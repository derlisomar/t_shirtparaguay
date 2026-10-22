<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Categoría de la tienda. Una categoría puede tener líneas adentro
 * (por ejemplo Remeras > Clásicas hilo 40.1, Oversize 30.1…), que son
 * categorías con parent_id. Solo hay dos niveles.
 */
class Category extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'description', 'image', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'parent_id' => 'integer'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** Solo las categorías principales (las que van en el menú) */
    public function scopeRoots($q)
    {
        return $q->whereNull('parent_id');
    }

    /** Ids de esta categoría y de sus líneas visibles, para listar todos sus productos */
    public function familyIds(bool $onlyVisible = true): array
    {
        return array_merge([$this->id], $this->children()->when($onlyVisible, fn ($q) => $q->where('is_active', true))->pluck('id')->all());
    }

    /** Nombre con la categoría principal adelante: «Remeras › Oversize 30.1» */
    public function fullName(): string
    {
        return $this->parent ? $this->parent->name.' › '.$this->name : $this->name;
    }

    /**
     * Todas las categorías en orden de árbol: cada principal seguida de sus líneas.
     * Cada una trae «depth» (0 principal, 1 línea) para mostrarlas con sangría.
     */
    public static function tree(?callable $scope = null)
    {
        $q = static::query()->orderBy('sort_order')->orderBy('name');
        if ($scope) {
            $scope($q);
        }
        $all = $q->get();
        $out = collect();
        foreach ($all->whereNull('parent_id') as $root) {
            $out->push(tap($root, fn ($c) => $c->depth = 0));
            foreach ($all->where('parent_id', $root->id) as $child) {
                $out->push(tap($child, fn ($c) => $c->depth = 1));
            }
        }

        return $out;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
