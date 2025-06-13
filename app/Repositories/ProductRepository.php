<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getProductsByCategory(?string $category = null)
    {
        $query = $this->model->query();
        
        if ($category) {
            $query->where('category', $category);
        }
        
        return $query->get();
    }

    public function getAvailableProducts()
    {
        return $this->model->where('stock', '>', 0)->get();
    }

    public function updateStock(int $id, int $quantity)
    {
        $product = $this->find($id);
        if ($product) {
            $product->stock -= $quantity;
            $product->save();
            return true;
        }
        return false;
    }
} 