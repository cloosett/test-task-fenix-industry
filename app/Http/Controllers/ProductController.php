<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(): JsonResponse
    {
        $products = $this->productService->getFilteredProducts(request());
        return response()->json($products);
    }

    public function show(Product $product): View
    {
        if (!$this->productService->checkProductAvailability($product)) {
            abort(404);
        }

        return view('products.show', compact('product'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->createProduct($request->validated());
        return redirect()->route('home')->with('success', 'Товар успішно додано!');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->updateProduct($product->id, $request->validated());
        return redirect()->back()->with('success', 'Товар оновлено!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->deleteProduct($product->id);
        return redirect()->route('home')->with('success', 'Товар видалено!');
    }
}
