<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public static function getForUserOrSession($userId = null, $sessionId = null)
    {
        $query = static::query();
        
        if ($userId) {
            // For authenticated users, get cart by user_id only
            $query->where('user_id', $userId)->whereNull('session_id');
        } else {
            // For guests, get cart by session_id and user_id is null
            $query->where('session_id', $sessionId)->whereNull('user_id');
        }
        
        return $query->with('items.product')->first();
    }

    public static function createForUserOrSession($userId = null, $sessionId = null)
    {
        return static::create([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId
        ]);
    }

    /**
     * Get guest cart for merging
     */
    public static function getGuestCart($sessionId)
    {
        return static::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->with('items.product')
            ->first();
    }

    /**
     * Get or create user cart
     */
    public static function getOrCreateUserCart($userId)
    {
        return static::firstOrCreate([
            'user_id' => $userId,
            'session_id' => null
        ]);
    }
}
