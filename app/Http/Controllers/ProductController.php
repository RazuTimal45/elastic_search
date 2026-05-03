<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query');

        $products = $query
            ? Product::search($query)->paginate(10)
            : Product::paginate(10);

        return view('products.search', compact('products', 'query'));
    }

    public function seed()
    {
        Product::withoutSyncingToSearch(function () {
            Product::factory()->count(50)->create();
        });

        return back()->with('success', '50 products seeded to database! Note: They are not indexed in Elasticsearch yet.');
    }
}
