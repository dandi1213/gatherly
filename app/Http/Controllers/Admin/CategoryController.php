<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::of(Category::query())
                ->filterColumn('name', function ($model) use ($request) {
                    $model->where('name', 'like', '%'.$request->get('search')['value'].'%');
                })
                ->editColumn('events_count', fn ($model) => $model->events()->count())
                ->editColumn('actions', fn ($model) => view('admin.categories.partials._actions', ['category' => $model]))
                ->rawColumns(['actions'])
                ->make(true);
        }

        $categories = Category::latest()->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')],
        ]);

        Category::create($data);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category)],
        ]);

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $data) && $data[$key] !== null) {
                $category->$key = $data[$key];
            }
        }

        $category->save();

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->events()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus kategori yang masih digunakan event.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
