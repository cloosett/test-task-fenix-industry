<?php

namespace App\DTOs;

class CartSummaryDTO
{
    public function __construct(
        public readonly int $totalItems,
        public readonly float $total,
        public readonly string $formattedTotal
    ) {}

    public static function fromCart($cart): self
    {
        return new self(
            totalItems: $cart->total_items,
            total: $cart->total,
            formattedTotal: number_format($cart->total, 0, ',', ' ') . ' ₴'
        );
    }

    public function toArray(): array
    {
        return [
            'total_items' => $this->totalItems,
            'total' => $this->total,
            'formatted_total' => $this->formattedTotal
        ];
    }
} 