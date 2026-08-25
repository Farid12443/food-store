<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Category;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    /**
     * index
     *
     * @param  mixed $request
     * @return void
     */
    public function index()
    {
        //get categories
        $categories = Category::orderBy('name', 'asc')->get();

        //return response JSON categories
        return response()->json([
            'success'   => true,
            'message'   => 'List Kategori',
            'data'      => $categories
        ]);
    }

    /**
     * show
     *
     * @param  mixed $slug
     * @return void
     */
    public function show($slug)
    {
        //get category
        $category = Category::query()
            ->with('products')
            ->where('slug', $slug)
            ->firstOrFail();

        //return response JSON category
        return response()->json([
            'success'   => true,
            'message'   => 'List Produk Kategori ' . $category->name,
            'data'      => $category
        ]);
    }
}
