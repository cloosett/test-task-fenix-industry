<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Repositories\CartRepository;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    protected CartRepository $cartRepository;
    protected ProductService $productService;

    public function __construct(CartRepository $cartRepository, ProductService $productService)
    {
        $this->cartRepository = $cartRepository;
        $this->productService = $productService;
    }

    public function getOrCreateCart(?int $userId, ?string $sessionId): Cart
    {
        $cart = $this->cartRepository->getForUserOrSession($userId, $sessionId);
        
        if (!$cart) {
            $cart = $this->cartRepository->createForUserOrSession($userId, $sessionId);
        }

        return $cart;
    }

    public function addToCart(?int $userId, ?string $sessionId, int $productId, int $quantity = 1): array
    {
        $product = $this->productService->getProduct($productId);
        
        if (!$product || !$product->is_active || $product->stock < $quantity) {
            return ['success' => false, 'message' => 'Товар недоступний або недостатньо на складі'];
        }

        $cart = $this->getOrCreateCart($userId, $sessionId);
        $cartItem = $this->cartRepository->findCartItem($cart->id, $productId);

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            if ($newQuantity > $product->stock) {
                return ['success' => false, 'message' => 'Недостатньо товару на складі'];
            }
            $this->cartRepository->updateItemQuantity($cartItem->id, $newQuantity);
        } else {
            $this->cartRepository->addItem($cart->id, $productId, $quantity);
        }

        return ['success' => true, 'message' => 'Товар додано до кошика!'];
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): array
    {
        if ($quantity > $cartItem->product->stock) {
            return ['success' => false, 'message' => 'Недостатньо товару на складі'];
        }

        $this->cartRepository->updateItemQuantity($cartItem->id, $quantity);
        $cartItem->refresh();

        return [
            'success' => true,
            'item' => [
                'id' => $cartItem->id,
                'quantity' => $cartItem->quantity,
                'subtotal' => $cartItem->quantity * $cartItem->product->price,
                'formatted_subtotal' => number_format($cartItem->quantity * $cartItem->product->price, 0, ',', ' ') . ' ₴'
            ],
            'cart' => $this->getCartSummary($cartItem->cart)
        ];
    }

    public function removeFromCart(CartItem $cartItem): array
    {
        $cart = $cartItem->cart;
        $this->cartRepository->removeItem($cartItem->id);
        $cart->refresh();

        return [
            'success' => true,
            'cart' => $this->getCartSummary($cart)
        ];
    }

    public function clearCart(Cart $cart): bool
    {
        return $this->cartRepository->clearCart($cart->id);
    }

    public function getCartItems(Cart $cart): Collection
    {
        return $cart->items()->with('product')->get();
    }

    public function getCartSummary(Cart $cart): array
    {
        return [
            'total_items' => $cart->total_items,
            'total' => $cart->total,
            'formatted_total' => number_format($cart->total, 0, ',', ' ') . ' ₴'
        ];
    }

    public function mergeGuestCart(?int $userId, ?string $sessionId): bool
    {
        if (!$userId) {
            return false;
        }

        $guestCart = $this->cartRepository->getForUserOrSession(null, $sessionId);
        
        if (!$guestCart) {
            return false;
        }

        $userCart = $this->getOrCreateCart($userId, null);

        foreach ($guestCart->items as $guestItem) {
            $userItem = $this->cartRepository->findCartItem($userCart->id, $guestItem->product_id);
            
            if ($userItem) {
                $this->cartRepository->updateItemQuantity(
                    $userItem->id, 
                    $userItem->quantity + $guestItem->quantity
                );
            } else {
                $this->cartRepository->addItem(
                    $userCart->id, 
                    $guestItem->product_id, 
                    $guestItem->quantity
                );
            }
        }

        $this->cartRepository->delete($guestCart->id);
        return true;
    }
} 