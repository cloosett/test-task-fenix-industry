<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PageController extends Controller
{
    protected ProductService $productService;
    protected CartService $cartService;

    public function __construct(ProductService $productService, CartService $cartService)
    {
        $this->productService = $productService;
        $this->cartService = $cartService;
    }

    /**
     * Show the home page with products.
     */
    public function home(Request $request): View
    {
        $category = $request->get('category');
        $products = $this->productService->getProducts($category);
        $categories = ['electronics', 'clothing', 'shoes', 'home'];
        
        $cartCount = $this->getCartCount($request);
        $cartItems = $this->getCartItems($request);

        return view('pages.home', compact('products', 'categories', 'cartCount', 'cartItems'));
    }

    /**
     * Show the cart page.
     */
    public function cart(Request $request): View
    {
        $cart = $this->getCart($request);
        $cartItems = $cart ? $this->cartService->getCartItems($cart) : collect();
        $total = $cart ? $cart->total : 0;
        $totalItems = $cart ? $cart->total_items : 0;

        return view('pages.cart', compact('cartItems', 'total', 'totalItems'));
    }

    private function getCart(Request $request)
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();
        return $this->cartService->getOrCreateCart($userId, $sessionId);
    }

    /**
     * Get current cart items count.
     */
    private function getCartCount(Request $request): int
    {
        $cart = $this->getCart($request);
        return $cart ? $cart->total_items : 0;
    }

    private function getCartItems(Request $request): array
    {
        $cart = $this->getCart($request);
        return $cart ? $cart->items()->pluck('quantity', 'product_id')->toArray() : [];
    }
}
