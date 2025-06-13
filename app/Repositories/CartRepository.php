<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;

class CartRepository extends BaseRepository
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function getForUserOrSession(?int $userId, ?string $sessionId): ?Cart
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->first();
    }

    public function createForUserOrSession(?int $userId, ?string $sessionId): Cart
    {
        return $this->create([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
        ]);
    }

    public function findCartItem(int $cartId, int $productId): ?CartItem
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
    }

    public function addItem(int $cartId, int $productId, int $quantity): CartItem
    {
        return CartItem::create([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    public function updateItemQuantity(int $itemId, int $quantity): bool
    {
        return CartItem::where('id', $itemId)->update(['quantity' => $quantity]);
    }

    public function removeItem(int $itemId): bool
    {
        return CartItem::destroy($itemId);
    }

    public function clearCart(int $cartId): bool
    {
        return CartItem::where('cart_id', $cartId)->delete();
    }
} 