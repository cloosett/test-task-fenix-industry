<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateQuantityRequest;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function addToCart(AddToCartRequest $request): RedirectResponse
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();
        $quantity = $request->validated()['quantity'] ?? 1;

        $result = $this->cartService->addToCart(
            $userId,
            $sessionId,
            $request->validated()['product_id'],
            $quantity
        );

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function updateQuantity(UpdateQuantityRequest $request, CartItem $cartItem): RedirectResponse
    {
        $cart = $this->getCartForCurrentUser($request);

        if ($cartItem->cart_id !== $cart->id) {
            return redirect()->route('cart')->with('error', 'Товар не знайдено в кошику');
        }

        $result = $this->cartService->updateQuantity($cartItem, $request->validated()['quantity']);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Кількість оновлено');
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function updateQuantityAjax(UpdateQuantityRequest $request, CartItem $cartItem): JsonResponse
    {
        $cart = $this->getCartForCurrentUser($request);

        if ($cartItem->cart_id !== $cart->id) {
            return response()->json(['error' => 'Товар не знайдено в кошику'], 404);
        }

        $result = $this->cartService->updateQuantity($cartItem, $request->validated()['quantity']);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json(['error' => $result['message']], 400);
    }

    public function removeFromCart(Request $request, CartItem $cartItem): RedirectResponse
    {
        $cart = $this->getCartForCurrentUser($request);

        if ($cartItem->cart_id !== $cart->id) {
            return redirect()->route('cart')->with('error', 'Товар не знайдено в кошику');
        }

        $this->cartService->removeFromCart($cartItem);
        return redirect()->back()->with('success', 'Товар видалено з кошика');
    }

    public function removeFromCartAjax(Request $request, CartItem $cartItem): JsonResponse
    {
        $cart = $this->getCartForCurrentUser($request);

        if ($cartItem->cart_id !== $cart->id) {
            return response()->json(['error' => 'Товар не знайдено в кошику'], 404);
        }

        $result = $this->cartService->removeFromCart($cartItem);
        return response()->json($result);
    }

    public function getCartSummary(Request $request): JsonResponse
    {
        $cart = $this->getCartForCurrentUser($request);
        return response()->json($this->cartService->getCartSummary($cart));
    }

    public function clearCart(Request $request): RedirectResponse
    {
        $cart = $this->getCartForCurrentUser($request);
        $this->cartService->clearCart($cart);
        return redirect()->route('cart')->with('success', 'Кошик очищено');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cart = $this->getCartForCurrentUser($request);
        $cartItems = $this->cartService->getCartItems($cart);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Кошик порожній');
        }

        $this->cartService->clearCart($cart);
        return redirect()->route('home')->with('success', 'Замовлення успішно оформлено!');
    }

    public function mergeGuestCart(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Необхідно увійти в систему');
        }

        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $result = $this->cartService->mergeGuestCart($userId, $sessionId);

        if ($result) {
            return redirect()->route('cart')->with('success', 'Кошик об\'єднано успішно');
        }

        return redirect()->back()->with('info', 'Гостьовий кошик не знайдено');
    }

    private function getCartForCurrentUser(Request $request)
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();
        return $this->cartService->getOrCreateCart($userId, $sessionId);
    }
}

