<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $category = $request->get('category');
        $products = $this->productService->getProducts($category);
        $categories = ['electronics', 'clothing', 'shoes', 'home'];

        return view('pages.home', compact('products', 'categories'));
    }
} 