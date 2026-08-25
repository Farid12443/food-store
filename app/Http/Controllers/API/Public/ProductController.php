<?php

namespace App\Http\Controllers\Api\Public;

use App\Models\Product;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * index
     *
     * @return void
     */
    public function index()
    {
        //get products
        $products = Product::query()
            ->with('category', 'ratings.customer')
            ->withAvg('ratings', 'rating')
            ->when(request()->has('search'), function ($query) {
                $query->where('title', 'like', '%' . request()->search . '%');
            })
            ->paginate(5);

        // format avg_rating ke satu desimal
        $products->getCollection()->transform(function ($product) {
            $product->ratings_avg_rating = number_format((float)$product->ratings_avg_rating, 1);
            return $product;
        });

        //return response JSON products
        return response()->json([
            'success'   => true,
            'message'   => 'List Produk',
            'data'      => $products
        ]);
    }

    /**
     * productPopular
     *
     * @return void
     */
    public function productPopular()
    {
        // Get popular products
        $products = Product::query()
            ->with('category', 'ratings.customer')
            ->withAvg('ratings', 'rating')
            ->withCount(['ratings' => function ($query) {
                $query->where('rating', '>=', 4); // Count ratings >= 4 only
            }])
            ->when(request()->has('search'), function ($query) {
                $query->where('title', 'like', '%' . request()->search . '%');
            })
            ->orderBy('ratings_count', 'desc') // Sort by count of ratings >= 4
            ->limit(5)
            ->get();

        // Format avg_rating to one decimal place
        $products->transform(function ($product) {
            $product->ratings_avg_rating = number_format((float)$product->ratings_avg_rating, 1);
            return $product;
        });

        // Return response JSON products
        return response()->json([
            'success' => true,
            'message' => 'List Produk Terpopuler',
            'data' => $products
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
        //get product by slug
        $product = Product::query()
            ->with('category', 'ratings.customer')
            ->withCount('ratings')
            ->withAvg('ratings', 'rating')
            ->where('slug', $slug)
            ->firstOrFail();

        // Format ratings_avg_rating ke satu desimal
        $product->ratings_avg_rating = number_format((float)$product->ratings_avg_rating, 1);

        //return response JSON product
        return response()->json([
            'success'   => true,
            'message'   => 'Detail Produk',
            'data'      => $product
        ]);
    }
}
