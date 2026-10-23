<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// ---------- Tienda ----------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tienda', [CatalogController::class, 'index'])->name('catalog');
Route::get('/categoria/{category}', [CatalogController::class, 'index'])->name('category');
Route::get('/producto/{product}', [CatalogController::class, 'show'])->name('product.show');
Route::get('/buscar', [CatalogController::class, 'search'])->name('search');
Route::view('/guia-de-talles', 'shop.size-guide')->name('size-guide');
Route::get('/ayuda', [HomeController::class, 'help'])->name('help');

Route::get('/carrito', [CartController::class, 'index'])->name('cart');
Route::get('/carrito/panel', [CartController::class, 'drawer'])->name('cart.drawer');
Route::post('/carrito', [CartController::class, 'add'])->name('cart.add');
Route::patch('/carrito/{variant}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrito/{variant}', [CartController::class, 'remove'])->name('cart.remove');

// Cuentas de clientes
Route::middleware('guest')->group(function () {
    Route::get('/ingresar', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/ingresar', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/crear-cuenta', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/crear-cuenta', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::post('/salir', [AuthController::class, 'logout'])->name('logout');

// Se puede finalizar la compra sin cuenta: al confirmar se crea con los datos del cliente
Route::get('/finalizar-compra', [CheckoutController::class, 'show'])->name('checkout');
Route::post('/finalizar-compra', [CheckoutController::class, 'store'])->name('checkout.store')->middleware('throttle:20,1');

Route::middleware('auth')->group(function () {
    Route::get('/pedido/{order}/gracias', [CheckoutController::class, 'thanks'])->name('checkout.thanks');
    Route::get('/pedido/{order}/boleta', [CheckoutController::class, 'invoice'])->name('checkout.invoice');

    Route::get('/mi-cuenta', [AccountController::class, 'index'])->name('account');
    Route::get('/mi-cuenta/pedidos/{order}', [AccountController::class, 'order'])->name('account.order');
    Route::post('/mi-cuenta/pedidos/{order}/comprobante', [AccountController::class, 'uploadReceipt'])->name('account.receipt.store');
    Route::get('/mi-cuenta/pedidos/{order}/comprobante', [AccountController::class, 'receipt'])->name('account.receipt');
    Route::put('/mi-cuenta/datos', [AccountController::class, 'update'])->name('account.update');
    Route::put('/mi-cuenta/clave', [AccountController::class, 'password'])->name('account.password');

    Route::get('/favoritos', [FavoriteController::class, 'index'])->name('favorites');
    Route::post('/favoritos/{product}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

// Pagos con tarjeta (preparado para la segunda actualización)
Route::get('/pagos/{gateway}/retorno', [PaymentController::class, 'return'])->name('payments.return');
Route::post('/pagos/{gateway}/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

// ---------- Panel de administración ----------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/ingresar', [AuthController::class, 'showAdminLogin'])->name('login');
    Route::post('/ingresar', [AuthController::class, 'adminLogin'])->middleware('throttle:10,1');

    Route::middleware('admin')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.reports'))->name('dashboard');
        Route::get('reportes', [Admin\ReportController::class, 'index'])->name('reports');
        Route::get('reportes/exportar/{type}', [Admin\ReportController::class, 'export'])->name('reports.export');

        Route::get('productos/sugerir-codigo', [Admin\ProductController::class, 'suggestSku'])->name('products.sku');
        Route::resource('productos', Admin\ProductController::class)
            ->parameters(['productos' => 'product'])->except('show')->names('products');
        Route::delete('productos/{product}/fotos/{image}', [Admin\ProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::post('productos/{product}/duplicar', [Admin\ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::get('stock', [Admin\StockController::class, 'index'])->name('stock');
        Route::post('stock', [Admin\StockController::class, 'update'])->name('stock.update');

        Route::resource('categorias', Admin\CategoryController::class)
            ->parameters(['categorias' => 'category'])->except('show')->names('categories');

        Route::get('talles-y-colores', [Admin\AttributeController::class, 'index'])->name('attributes');
        Route::post('talles', [Admin\AttributeController::class, 'storeSize'])->name('sizes.store');
        Route::put('talles/{size}', [Admin\AttributeController::class, 'updateSize'])->name('sizes.update');
        Route::delete('talles/{size}', [Admin\AttributeController::class, 'destroySize'])->name('sizes.destroy');
        Route::post('colores', [Admin\AttributeController::class, 'storeColor'])->name('colors.store');
        Route::put('colores/{color}', [Admin\AttributeController::class, 'updateColor'])->name('colors.update');
        Route::delete('colores/{color}', [Admin\AttributeController::class, 'destroyColor'])->name('colors.destroy');

        Route::resource('slider', Admin\SlideController::class)
            ->parameters(['slider' => 'slide'])->except('show')->names('slides');
        Route::post('slider/orden', [Admin\SlideController::class, 'reorder'])->name('slides.reorder');

        Route::get('pedidos', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::get('pedidos/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::put('pedidos/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
        Route::get('pedidos/{order}/ticket', [Admin\OrderController::class, 'ticket'])->name('orders.ticket');
        Route::put('pedidos/{order}/prendas', [Admin\OrderController::class, 'updateItems'])->name('orders.items');
        Route::post('pedidos/{order}/finalizar', [Admin\OrderController::class, 'finish'])->name('orders.finish');
        Route::post('pedidos/{order}/procesar', [Admin\OrderController::class, 'process'])->name('orders.process');
        Route::get('pedidos/{order}/comprobante', [Admin\OrderController::class, 'receipt'])->name('orders.receipt');

        Route::get('vender', [Admin\PosController::class, 'index'])->name('pos');
        Route::get('vender/buscar', [Admin\PosController::class, 'search'])->name('pos.search');
        Route::get('vender/clientes', [Admin\PosController::class, 'customers'])->name('pos.customers');
        Route::post('vender', [Admin\PosController::class, 'store'])->name('pos.store');

        Route::get('clientes', [Admin\CustomerController::class, 'index'])->name('customers.index');
        Route::get('clientes/nuevo', [Admin\CustomerController::class, 'create'])->name('customers.create');
        Route::post('clientes', [Admin\CustomerController::class, 'store'])->name('customers.store');
        Route::get('clientes/{user}', [Admin\CustomerController::class, 'show'])->whereNumber('user')->name('customers.show');
        Route::put('clientes/{user}', [Admin\CustomerController::class, 'update'])->whereNumber('user')->name('customers.update');
        Route::delete('clientes/{user}', [Admin\CustomerController::class, 'destroy'])->whereNumber('user')->name('customers.destroy');

        Route::get('configuracion', [Admin\SettingController::class, 'edit'])->name('settings');
        Route::put('configuracion', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('usuarios', [Admin\SettingController::class, 'admins'])->name('admins');
        Route::post('usuarios', [Admin\SettingController::class, 'storeAdmin'])->name('admins.store');
        Route::delete('usuarios/{user}', [Admin\SettingController::class, 'destroyAdmin'])->name('admins.destroy');
    });
});
