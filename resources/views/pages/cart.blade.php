@extends('layouts.app')

@section('title', 'Кошик')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Ваш кошик</h2>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Повернутися до покупок
                    </a>
                </div>

                <div id="cart-items">
                    @forelse($cartItems as $item)
                        <div class="cart-item p-3 border rounded mb-3" data-item-id="{{ $item->id }}"
                            style="overflow: visible;">
                            <div class="row align-items-center" style="overflow: visible;">
                                <div class="col-md-2">
                                    <img src="{{ $item->product->image }}" class="cart-item-image w-100"
                                        alt="{{ $item->product->name }}">
                                </div>
                                <div class="col-md-3">
                                    <h6 class="mb-1">{{ $item->product->name }}</h6>
                                    <small class="text-muted">{{ $item->product->category }}</small>
                                    <p class="mb-0 text-primary fw-bold">
                                        {{ number_format($item->product->price, 0, ',', ' ') }} ₴</p>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <label class="me-2 text-nowrap">Кількість:</label>
                                        <div class="quantity-controls d-flex align-items-center"
                                            style="position: relative; z-index: 10;">
                                            <button type="button"
                                                class="btn btn-outline-secondary btn-sm quantity-btn-minus"
                                                data-item-id="{{ $item->id }}"
                                                data-current-quantity="{{ $item->quantity }}"
                                                style="min-width: 32px; height: 32px;">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control quantity-input mx-1"
                                                value="{{ $item->quantity }}" min="1"
                                                max="{{ $item->product->stock }}" data-item-id="{{ $item->id }}"
                                                style="width: 70px; text-align: center;">
                                            <button type="button"
                                                class="btn btn-outline-secondary btn-sm quantity-btn-plus"
                                                data-item-id="{{ $item->id }}"
                                                data-current-quantity="{{ $item->quantity }}"
                                                data-max-stock="{{ $item->product->stock }}"
                                                style="min-width: 32px; height: 32px;">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <div class="fw-bold text-primary mb-2 item-subtotal">
                                        {{ number_format($item->total, 0, ',', ' ') }} ₴
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn"
                                        data-item-id="{{ $item->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state text-center py-5" id="empty-cart-message">
                            <i class="fas fa-shopping-cart display-1 text-muted opacity-50"></i>
                            <h3 class="mt-3">Ваш кошик порожній</h3>
                            <p class="text-muted">Додайте товари, щоб почати покупки</p>
                            <a href="{{ route('home') }}" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Почати покупки
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm position-sticky" style="top: 100px;">
                    <div class="card-body">
                        <h5 class="card-title">Підсумок замовлення</h5>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Товарів:</span>
                            <span id="cart-total-items">{{ $totalItems }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fw-bold">Загальна сума:</span>
                            <span class="fw-bold text-primary" id="cart-total">{{ number_format($total, 0, ',', ' ') }}
                                ₴</span>
                        </div>

                        @if ($cartItems->isNotEmpty())
                            <form action="{{ route('cart.checkout') }}" method="POST" id="checkout-form">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 btn-lg mb-2">
                                    <i class="fas fa-credit-card me-2"></i>Оформити замовлення
                                </button>
                            </form>

                            <form action="{{ route('cart.clear') }}" method="POST" id="clear-cart-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Ви впевнені, що хочете очистити кошик?')">
                                    <i class="fas fa-trash me-2"></i>Очистити кошик
                                </button>
                            </form>
                        @else
                            <button class="btn btn-primary w-100 btn-lg" id="checkout-btn-disabled" disabled>
                                <i class="fas fa-credit-card me-2"></i>Оформити замовлення
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cartContainer = document.getElementById('cart-items');
            const totalItemsElement = document.getElementById('cart-total-items');
            const totalElement = document.getElementById('cart-total');
            const checkoutForm = document.getElementById('checkout-form');
            const clearCartForm = document.getElementById('clear-cart-form');
            const checkoutBtnDisabled = document.getElementById('checkout-btn-disabled');

            function updateQuantity(itemId, newQuantity) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/cart/items/${itemId}/ajax`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            quantity: newQuantity
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                            const subtotalElement = itemElement.querySelector('.item-subtotal');
                            subtotalElement.textContent = data.item.formatted_subtotal;

                            totalItemsElement.textContent = data.cart.total_items;
                            totalElement.textContent = data.cart.formatted_total;

                            updateNavbarCartCount(data.cart.total_items);

                            updateQuantityButtons(itemId, data.item.quantity);
                        } else {
                            alert(data.error || 'Помилка при оновленні кількості');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Помилка при оновленні кількості');
                    });
            }

            function removeItem(itemId) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/cart/items/${itemId}/ajax`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                            itemElement.remove();

                            totalItemsElement.textContent = data.cart.total_items;
                            totalElement.textContent = data.cart.formatted_total;

                            updateNavbarCartCount(data.cart.total_items);

                            if (data.cart.total_items === 0) {
                                showEmptyCartMessage();
                            }
                        } else {
                            alert(data.error || 'Помилка при видаленні товару');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Помилка при видаленні товару');
                    });
            }

            function updateNavbarCartCount(count) {
                const navbarBadge = document.querySelector('.navbar .badge');
                if (navbarBadge) {
                    navbarBadge.textContent = count;
                }
            }

            function updateQuantityButtons(itemId, quantity) {
                const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                const minusBtn = itemElement.querySelector('.quantity-btn-minus');
                const plusBtn = itemElement.querySelector('.quantity-btn-plus');
                const input = itemElement.querySelector('.quantity-input');

                minusBtn.disabled = quantity <= 1;
                const maxStock = parseInt(plusBtn.getAttribute('data-max-stock'));
                plusBtn.disabled = quantity >= maxStock;

                minusBtn.setAttribute('data-current-quantity', quantity);
                plusBtn.setAttribute('data-current-quantity', quantity);
                input.value = quantity;
            }

            function showEmptyCartMessage() {
                cartContainer.innerHTML = `
            <div class="empty-state text-center py-5" id="empty-cart-message">
                <i class="fas fa-shopping-cart display-1 text-muted opacity-50"></i>
                <h3 class="mt-3">Ваш кошик порожній</h3>
                <p class="text-muted">Додайте товари, щоб почати покупки</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Почати покупки
                </a>
            </div>
        `;

                if (checkoutForm) checkoutForm.style.display = 'none';
                if (clearCartForm) clearCartForm.style.display = 'none';
                if (checkoutBtnDisabled) checkoutBtnDisabled.style.display = 'block';
            }

            cartContainer.addEventListener('click', function(e) {
                const target = e.target.closest('button');
                if (!target) return;

                const itemId = target.getAttribute('data-item-id');

                if (target.classList.contains('quantity-btn-plus')) {
                    const currentQuantity = parseInt(target.getAttribute('data-current-quantity'));
                    const maxStock = parseInt(target.getAttribute('data-max-stock'));

                    if (!isNaN(currentQuantity) && !isNaN(maxStock) && currentQuantity < maxStock) {
                        updateQuantity(itemId, currentQuantity + 1);
                    } else if (currentQuantity >= maxStock) {
                        alert('Досягнуто максимальну кількість товару в наявності');
                    }
                } else if (target.classList.contains('quantity-btn-minus')) {
                    const currentQuantity = parseInt(target.getAttribute('data-current-quantity'));

                    if (!isNaN(currentQuantity) && currentQuantity > 1) {
                        updateQuantity(itemId, currentQuantity - 1);
                    }
                } else if (target.classList.contains('remove-item-btn')) {
                    if (confirm('Ви впевнені, що хочете видалити цей товар з кошика?')) {
                        removeItem(itemId);
                    }
                }
            });

            cartContainer.addEventListener('change', function(e) {
                if (e.target.classList.contains('quantity-input')) {
                    const itemId = e.target.dataset.itemId;
                    const newQuantity = parseInt(e.target.value);

                    if (newQuantity > 0) {
                        updateQuantity(itemId, newQuantity);
                    }
                }
            });
        });
    </script>
@endpush
