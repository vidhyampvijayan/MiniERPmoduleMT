<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->paginate(15);

        return view('inventory.index', compact('products'));
    }
}
