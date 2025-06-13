# ShopUA - E-commerce Platform

Сучасна e-commerce платформа, побудована на Laravel з використанням принципів Clean Architecture та SOLID.

## 🏗️ Архітектура проекту

### Поточна структура

```
app/
├── DTOs/                    # Data Transfer Objects
├── Http/
│   ├── Controllers/         # HTTP контролери (тонкий шар)
│   └── Requests/           # Валідація запитів
├── Models/                 # Eloquent моделі
├── Repositories/           # Шар доступу до даних
└── Services/              # Бізнес-логіка
```

### Принципи реалізації

- **Repository Pattern** - відокремлення логіки доступу до даних
- **Service Layer** - централізація бізнес-логіки
- **Request Classes** - валідація даних на рівні HTTP
- **DTO Pattern** - типізація передачі даних
- **Dependency Injection** - слабка зв'язаність компонентів

## ✅ Реалізований функціонал

### Управління продуктами
- CRUD операції з товарами
- Фільтрація за категоріями
- Перевірка наявності на складі
- Типізовані моделі з бізнес-логікою

### Система кошика
- Додавання/видалення товарів
- Динамічне оновлення без перезавантаження (AJAX)
- Збереження кошика для гостей (session) та користувачів (database)
- Автоматичне об'єднання гостьового кошика при авторизації

### Авторизація
- Laravel Breeze для аутентифікації
- Rate limiting для захисту від брутфорс атак
- Автоматична інтеграція кошика при логіні

## 🔍 Аналіз стабільності коду

### ✅ Сильні сторони

1. **Чиста архітектура**
   - Розділення відповідальності між шарами
   - Слабка зв'язаність компонентів
   - Легкість тестування та розширення

2. **Типізація**
   - Строга типізація PHP 8.1+
   - Readonly властивості в DTO
   - Return type declarations

3. **Валідація**
   - Централізована валідація в Request класах
   - Локалізовані повідомлення про помилки
   - Серверна та клієнтська валідація

### ⚠️ Потенційні проблеми стабільності

#### 1. Відсутність тестування
```php
// Приклад відсутнього Unit тесту
class ProductServiceTest extends TestCase
{
    public function test_can_create_product()
    {
        // Тест не реалізований
    }
}
```

#### 2. Обробка помилок
- Відсутні try-catch блоки для критичних операцій
- Немає централізованої обробки винятків
- Відсутнє логування помилок

#### 3. База даних
- Немає індексів для оптимізації запитів
- Відсутні database constraints
- Немає резервного копіювання

#### 4. Моніторинг та логування
```php
// Потрібно додати:
Log::info('Product created', ['product_id' => $product->id]);
```

## 🚀 Рекомендації для стабілізації

### 1. Покриття тестами (Priority: HIGH)
```bash
# Unit тести для сервісів
php artisan make:test ProductServiceTest --unit

# Feature тести для API
php artisan make:test CartManagementTest
```

### 2. Обробка помилок
```php
// app/Exceptions/Handler.php
public function register()
{
    $this->reportable(function (Throwable $e) {
        Log::error('Application error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    });
}
```

### 3. Кешування
```php
// Кешування продуктів
Cache::remember("products_{$category}", 3600, function() {
    return $this->productRepository->getByCategory($category);
});
```

### 4. Database оптимізація
```php
// Міграція з індексами
Schema::table('products', function (Blueprint $table) {
    $table->index(['category', 'is_active']);
    $table->index('stock');
});
```

## 🛒 Детальний план розширення: Система управління замовленнями

### Поточна проблема
Зараз checkout просто очищає кошик без створення замовлення, що не відповідає реальним бізнес-потребам.

### Архітектура рішення

#### 1. Структура даних
```php
// database/migrations/create_orders_table.php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_number')->unique(); // ORD-2024-000001
    $table->foreignId('user_id')->nullable()->constrained();
    $table->string('customer_email');
    $table->string('customer_name');
    $table->string('customer_phone');
    $table->text('shipping_address');
    $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']);
    $table->decimal('subtotal', 10, 2);
    $table->decimal('shipping_cost', 8, 2)->default(0);
    $table->decimal('tax_amount', 8, 2)->default(0);
    $table->decimal('total_amount', 10, 2);
    $table->string('payment_method')->nullable();
    $table->string('payment_status')->default('pending');
    $table->timestamp('shipped_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->index(['status', 'created_at']);
    $table->index('order_number');
});

Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained();
    $table->string('product_name'); // Snapshot назви
    $table->decimal('product_price', 8, 2); // Snapshot ціни
    $table->integer('quantity');
    $table->decimal('total_price', 10, 2);
    $table->timestamps();
});
```

#### 2. Бізнес-логіка (Service Layer)
```php
// app/Services/OrderService.php
class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private CartService $cartService,
        private ProductService $productService,
        private NotificationService $notificationService
    ) {}

    public function createFromCart(Cart $cart, array $customerData): Order
    {
        DB::beginTransaction();
        
        try {
            // 1. Валідація наявності товарів
            $this->validateCartAvailability($cart);
            
            // 2. Створення замовлення
            $order = $this->orderRepository->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $cart->user_id,
                'customer_email' => $customerData['email'],
                'customer_name' => $customerData['name'],
                'customer_phone' => $customerData['phone'],
                'shipping_address' => $customerData['address'],
                'status' => 'pending',
                'subtotal' => $cart->total,
                'total_amount' => $this->calculateTotalWithShipping($cart->total),
            ]);
            
            // 3. Створення позицій замовлення
            foreach ($cart->items as $item) {
                $this->orderRepository->createOrderItem($order->id, [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_price' => $item->product->price,
                    'quantity' => $item->quantity,
                    'total_price' => $item->quantity * $item->product->price,
                ]);
                
                // 4. Зменшення залишків
                $this->productService->decreaseStock(
                    $item->product_id, 
                    $item->quantity
                );
            }
            
            // 5. Очищення кошика
            $this->cartService->clearCart($cart);
            
            // 6. Відправка notifications
            $this->notificationService->sendOrderConfirmation($order);
            
            DB::commit();
            
            return $order;
            
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Order creation failed', [
                'cart_id' => $cart->id,
                'error' => $e->getMessage()
            ]);
            throw new OrderCreationException('Не вдалося створити замовлення');
        }
    }

    public function updateStatus(Order $order, string $newStatus): void
    {
        $validTransitions = $this->getValidStatusTransitions($order->status);
        
        if (!in_array($newStatus, $validTransitions)) {
            throw new InvalidStatusTransitionException();
        }
        
        $order->update(['status' => $newStatus]);
        
        // Автоматичні дії при зміні статусу
        match($newStatus) {
            'shipped' => $this->handleShipped($order),
            'delivered' => $this->handleDelivered($order),
            'cancelled' => $this->handleCancelled($order),
            default => null
        };
        
        $this->notificationService->sendStatusUpdate($order);
    }
    
    private function generateOrderNumber(): string
    {
        $year = date('Y');
        $lastOrder = $this->orderRepository->getLastOrderForYear($year);
        $nextNumber = ($lastOrder?->sequence ?? 0) + 1;
        
        return sprintf('ORD-%s-%06d', $year, $nextNumber);
    }
}
```

#### 3. API для відстеження
```php
// app/Http/Controllers/Api/OrderTrackingController.php
class OrderTrackingController extends Controller
{
    public function track(string $orderNumber): JsonResponse
    {
        $order = $this->orderService->findByNumber($orderNumber);
        
        if (!$order) {
            return response()->json(['error' => 'Замовлення не знайдено'], 404);
        }
        
        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_display' => $order->getStatusDisplayName(),
            'created_at' => $order->created_at->format('d.m.Y H:i'),
            'estimated_delivery' => $order->getEstimatedDeliveryDate(),
            'timeline' => [
                [
                    'status' => 'pending',
                    'title' => 'Замовлення прийнято',
                    'completed' => true,
                    'date' => $order->created_at->format('d.m.Y')
                ],
                [
                    'status' => 'confirmed',
                    'title' => 'Замовлення підтверджено',
                    'completed' => $order->status_level >= 2,
                    'date' => $order->confirmed_at?->format('d.m.Y')
                ],
                [
                    'status' => 'shipped',
                    'title' => 'Відправлено',
                    'completed' => $order->status_level >= 4,
                    'date' => $order->shipped_at?->format('d.m.Y')
                ],
                [
                    'status' => 'delivered',
                    'title' => 'Доставлено',
                    'completed' => $order->status === 'delivered',
                    'date' => $order->delivered_at?->format('d.m.Y')
                ]
            ]
        ]);
    }
}
```

#### 4. Frontend компонент відстеження
```javascript
// resources/js/components/OrderTracking.js
class OrderTracking {
    constructor(orderNumber) {
        this.orderNumber = orderNumber;
        this.init();
    }
    
    async init() {
        try {
            const response = await fetch(`/api/orders/${this.orderNumber}/track`);
            const data = await response.json();
            
            if (response.ok) {
                this.renderTimeline(data.timeline);
                this.setupAutoRefresh();
            } else {
                this.showError(data.error);
            }
        } catch (error) {
            this.showError('Помилка завантаження даних');
        }
    }
    
    renderTimeline(timeline) {
        const container = document.getElementById('order-timeline');
        container.innerHTML = timeline.map(step => `
            <div class="timeline-step ${step.completed ? 'completed' : 'pending'}">
                <div class="step-icon">
                    ${step.completed ? '✓' : '○'}
                </div>
                <div class="step-content">
                    <h4>${step.title}</h4>
                    ${step.date ? `<span class="date">${step.date}</span>` : ''}
                </div>
            </div>
        `).join('');
    }
    
    setupAutoRefresh() {
        // Оновлення кожні 30 секунд для активних замовлень
        if (!['delivered', 'cancelled'].includes(this.currentStatus)) {
            setInterval(() => this.init(), 30000);
        }
    }
}
```

#### 5. Адмін панель для управління
```php
// app/Http/Controllers/Admin/OrderController.php
class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $this->orderService->getFilteredOrders([
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search'),
        ]);
        
        return view('admin.orders.index', compact('orders'));
    }
    
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        try {
            $this->orderService->updateStatus($order, $request->status);
            
            if ($request->notes) {
                $order->update(['notes' => $request->notes]);
            }
            
            return redirect()->back()->with('success', 'Статус замовлення оновлено');
        } catch (InvalidStatusTransitionException $e) {
            return redirect()->back()->with('error', 'Неможливо змінити статус');
        }
    }
}
```

### Переваги такої реалізації

1. **Відстеження життєвого циклу** - повна історія замовлення
2. **Автоматизація** - автоматичні зміни статусів та сповіщення
3. **Масштабованість** - легко додати нові статуси чи правила
4. **Аудит** - повний логи всіх змін
5. **UX** - клієнт завжди знає статус замовлення

### Технічні особливості

- **Database transactions** для атомарності операцій
- **Event-driven architecture** для сповіщень
- **Queue jobs** для важких операцій (emails, SMS)
- **API для мобільних додатків**
- **Webhook система** для інтеграції з платіжними системами

Ця система замовлень перетворить простий "магазин-каталог" у повноцінну e-commerce платформу з професійним рівнем сервісу.
