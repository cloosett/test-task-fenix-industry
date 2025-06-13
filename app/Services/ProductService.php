<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ProductService
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getProducts(?string $category = null): Collection
    {
        return $this->productRepository->getProductsByCategory($category);
    }

    public function getAvailableProducts(): Collection
    {
        return $this->productRepository->getAvailableProducts();
    }

    public function updateProductStock(int $productId, int $quantity): bool
    {
        return $this->productRepository->updateStock($productId, $quantity);
    }

    public function getProduct(int $productId): ?Product
    {
        return $this->productRepository->find($productId);
    }

    public function createProduct(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    public function updateProduct(int $productId, array $data): ?Product
    {
        return $this->productRepository->update($productId, $data);
    }

    public function deleteProduct(int $productId): bool
    {
        return $this->productRepository->delete($productId);
    }

    public function getFilteredProducts(Request $request): Collection
    {
        $query = Product::query()->active()->inStock();

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        return $query->latest()->get();
    }

    public function checkProductAvailability(Product $product): bool
    {
        return $product->is_active && $product->stock > 0;
    }
} 