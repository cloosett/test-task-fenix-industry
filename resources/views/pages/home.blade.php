@extends('layouts.app')

@section('title', 'Головна')

@section('content')
    <div class="container mt-4">
        
        <div class="hero-section bg-gradient text-white rounded-4 p-5 mb-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3 text-black">Найкращі товари для вас</h1>
                    <p class="lead mb-4 text-black">Відкрийте для себе широкий асортимент якісних товарів за найкращими
                        цінами</p>
                    <button class="btn btn-light btn-lg" onclick="document.getElementById('products').scrollIntoView()">
                        <i class="fas fa-shopping-bag me-2"></i>Переглянути товари
                    </button>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-shopping-cart display-1 opacity-50"></i>
                </div>
            </div>
        </div>

        
        <div id="products">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Каталог товарів</h2>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-2"></i>Фільтр
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('home') }}">Всі товари</a></li>
                        @foreach ($categories as $category)
                            <li><a class="dropdown-item" href="{{ route('home', ['category' => $category]) }}">
                                    {{ ucfirst($category === 'electronics' ? 'Електроніка' : ($category === 'clothing' ? 'Одяг' : ($category === 'shoes' ? 'Взуття' : 'Для дому'))) }}
                                </a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="row" id="products-container">
                @forelse($products as $product)
                    <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                        <div class="card product-card h-100">
                            <div class="position-relative">
                                <a href="{{ route('products.show', $product) }}">
                                    <img src="{{ $product->image }}" class="card-img-top product-image"
                                        alt="{{ $product->name }}">
                                </a>
                                <span class="category-badge">{{ ucfirst($product->category) }}</span>
                                @if ($product->stock <= 5)
                                    <span class="stock-badge {{ $product->stock == 0 ? 'stock-out' : 'stock-low' }}">
                                        {{ $product->stock == 0 ? 'Немає в наявності' : 'Мало на складі' }}
                                    </span>
                                @endif
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark">
                                        {{ $product->name }}
                                    </a>
                                </h5>
                                <p class="card-text product-description flex-grow-1">
                                    {{ Str::limit($product->description, 100) }}</p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="product-price">{{ number_format($product->price, 0, ',', ' ') }}
                                            ₴</span>
                                        <small class="text-muted">На складі: {{ $product->stock }}</small>
                                    </div>
                                    <div class="d-grid gap-2">
                                        @if ($product->stock > 0)
                                            @if (isset($cartItems[$product->id]))
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('cart') }}" class="btn btn-success flex-grow-1">
                                                        <i class="fas fa-check me-2"></i>В кошику
                                                        ({{ $cartItems[$product->id] }})
                                                    </a>
                                                </div>
                                            @else
                                                <form action="{{ route('cart.add') }}" method="POST"
                                                    class="add-to-cart-form" data-product-id="{{ $product->id }}">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="btn btn-primary w-100 btn-add-to-cart">
                                                        <i class="fas fa-cart-plus me-2"></i>Додати в кошик
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <button class="btn btn-secondary w-100" disabled>
                                                <i class="fas fa-times me-2"></i>Немає в наявності
                                            </button>
                                        @endif

                                        <a href="{{ route('products.show', $product) }}"
                                            class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-2"></i>Переглянути
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h3>Товари не знайдено</h3>
                            <p>Спробуйте змінити фільтр або повернутися пізніше</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const baseUrls = {
                cart: '{{ route('cart') }}',
                cartAdd: '{{ route('cart.add') }}',
                productShow: '{{ url('/products') }}'
            };

            const addToCartForms = document.querySelectorAll('.add-to-cart-form');

            addToCartForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const productId = this.dataset.productId;
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalContent = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Додавання...';

                    const formData = new FormData(this);

                    fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (response.redirected) {
                                window.location.reload();
                            } else {
                                return response.json();
                            }
                        })
                        .then(data => {
                            if (data && data.success) {
                                updateProductCardToInCart(productId, 1);
                                updateNavbarCartCount();
                            } else {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalContent;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            window.location.reload();
                        });
                });
            });

            function attachAddToCartHandler(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    window.location.reload();
                });
            }

            function updateNavbarCartCount() {
                fetch('/cart/summary', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const cartBadge = document.querySelector('.navbar .badge');
                        if (cartBadge) {
                            cartBadge.textContent = data.total_items;
                        }
                    })
                    .catch(error => console.error('Error updating cart count:', error));
            }
        });
    </script>
@endpush
