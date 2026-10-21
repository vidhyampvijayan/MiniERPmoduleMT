<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 15);

        $products = Product::query()
            ->orderBy('name')
            ->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function lowStock(Request $request)
    {
        $threshold = (int) $request->input('threshold', 10);
        $perPage = (int) $request->input('per_page', 15);

        $products = Product::query()
            ->lowStock($threshold)
            ->orderBy('stock_quantity')
            ->paginate($perPage);

        return ProductResource::collection($products);
    }
}
