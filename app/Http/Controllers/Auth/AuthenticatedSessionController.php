<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('pages.home');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $this->authService->login($request);

        return redirect()->intended(route('home'))->with('success', 'Ви успішно увійшли в систему!');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect()->route('home')->with('success', 'Ви вийшли з системи');
    }

    /**
     * Merge guest cart with user cart after login
     */
    private function mergeGuestCart(Request $request)
    {
        $sessionId = $request->session()->getId();
        $guestCart = \App\Models\Cart::where('session_id', $sessionId)->where('user_id', null)->first();
        
        if ($guestCart) {
            $userCart = \App\Models\Cart::firstOrCreate([
                'user_id' => Auth::id(),
                'session_id' => null
            ]);

            foreach ($guestCart->items as $guestItem) {
                $userItem = $userCart->items()->where('product_id', $guestItem->product_id)->first();
                
                if ($userItem) {
                    $userItem->update([
                        'quantity' => $userItem->quantity + $guestItem->quantity
                    ]);
                } else {
                    $userCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'quantity' => $guestItem->quantity
                    ]);
                }
            }

            $guestCart->delete();
        }
    }
}
