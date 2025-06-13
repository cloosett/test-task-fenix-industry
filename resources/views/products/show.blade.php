@extends('layouts.app')

@section('title', $product->name . ' - ShopUA')

@section('content')
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Головна</a></li>
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <img src="{{ $product->image }}" class="card-img-top" alt="{{ $product->name }}"
                        style="height: 400px; object-fit: cover;">
                </div>
            </div>

            <div class="col-md-6">
                <h1 class="mb-3">{{ $product->name }}</h1>

                <div class="mb-3">
                    <span class="badge bg-secondary">{{ ucfirst($product->category) }}</span>
                    @if ($product->stock <= 5)
                        <span class="badge {{ $product->stock == 0 ? 'bg-danger' : 'bg-warning' }}">
                            {{ $product->stock == 0 ? 'Немає в наявності' : 'Мало на складі' }}
                        </span>
                    @else
                        <span class="badge bg-success">В наявності</span>
                    @endif
                </div>

                <p class="lead">{{ $product->description }}</p>

                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h3 class="text-primary">{{ number_format($product->price, 0, ',', ' ') }} ₴</h3>
                    </div>
                    <div class="col-sm-6 text-end">
                        <small class="text-muted">На складі: {{ $product->stock }} шт.</small>
                    </div>
                </div>

                @if ($product->stock > 0)
                    <form action="{{ route('cart.add') }}" method="POST" class="mb-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Кількість:</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" value="1"
                                    min="1" max="{{ $product->stock }}">
                            </div>
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-cart-plus me-2"></i>Додати в кошик
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <button class="btn btn-secondary btn-lg w-100 mb-3" disabled>
                        <i class="fas fa-times me-2"></i>Немає в наявності
                    </button>
                @endif

                <div class="d-grid gap-2">
                    <a href="{{ route('cart') }}" class="btn btn-outline-primary">
                        <i class="fas fa-shopping-cart me-2"></i>Переглянути кошик
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Повернутися до каталогу
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <div class="mt-5">
            <h3>Схожі товари</h3>
            <div class="row">
                @php
                    $relatedProducts = App\Models\Product::where('category', $product->category)
                        ->where('id', '!=', $product->id)
                        ->active()
                        ->inStock()
                        ->take(4)
                        ->get();
                @endphp

                @foreach ($relatedProducts as $related)
                    <div class="col-md-3 mb-4">
                        <div class="card product-card h-100">
                            <img src="{{ $related->image }}" class="card-img-top product-image"
                                alt="{{ $related->name }}">
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title">{{ $related->name }}</h6>
                                <p class="card-text small text-muted flex-grow-1">
                                    {{ Str::limit($related->description, 60) }}</p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span
                                            class="fw-bold text-primary">{{ number_format($related->price, 0, ',', ' ') }}
                                            ₴</span>
                                    </div>
                                    <a href="{{ route('products.show', $related) }}"
                                        class="btn btn-outline-primary btn-sm w-100">
                                        Переглянути
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
