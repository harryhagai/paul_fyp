<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:seller'])->prefix('seller')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\seller\DashboardController::class, 'index'])->name('seller.dashboard');

    Route::get('/products', [\App\Http\Controllers\seller\ProductController::class, 'index'])->name('seller.products');
    Route::get('/products/create', [\App\Http\Controllers\seller\ProductController::class, 'create'])->name('seller.products.create');
    Route::post('/products', [\App\Http\Controllers\seller\ProductController::class, 'store'])->name('seller.products.store');
    Route::get('/products/{id}', [\App\Http\Controllers\seller\ProductController::class, 'show'])->name('seller.products.show');
    Route::get('/products/{id}/edit', [\App\Http\Controllers\seller\ProductController::class, 'edit'])->name('seller.products.edit');
    Route::get('/products/{id}/media', [\App\Http\Controllers\seller\ProductController::class, 'media'])->name('seller.products.media');
    Route::post('/products/{productId}/media/upload', [\App\Http\Controllers\seller\ProductController::class, 'uploadMedia'])->name('seller.products.media.upload');
    Route::patch('/products/{productId}/media/{mediaId}/primary', [\App\Http\Controllers\seller\ProductController::class, 'setPrimaryMedia'])->name('seller.products.media.primary');
    Route::delete('/products/{productId}/media/{mediaId}', [\App\Http\Controllers\seller\ProductController::class, 'deleteMedia'])->name('seller.products.media.delete');
    Route::put('/products/{id}', [\App\Http\Controllers\seller\ProductController::class, 'update'])->name('seller.products.update');
    Route::delete('/products/{id}', [\App\Http\Controllers\seller\ProductController::class, 'destroy'])->name('seller.products.destroy');
    Route::patch('/products/{id}/toggle-advertised', [\App\Http\Controllers\seller\ProductController::class, 'toggleAdvertised'])->name('seller.products.toggleAdvertised');

    Route::get('/orders', [\App\Http\Controllers\seller\OrderController::class, 'index'])->name('seller.orders');
    Route::get('/orders/{id}', [\App\Http\Controllers\seller\OrderController::class, 'show'])->name('seller.orders.show');
    Route::patch('/orders/{id}/status', [\App\Http\Controllers\seller\OrderController::class, 'updateStatus'])->name('seller.orders.updateStatus');

    Route::get('/customers', [\App\Http\Controllers\seller\CustomerController::class, 'index'])->name('seller.customers');
    Route::post('/customers', [\App\Http\Controllers\seller\CustomerController::class, 'store'])->name('seller.customers.store');
    Route::get('/customers/{id}', [\App\Http\Controllers\seller\CustomerController::class, 'show'])->name('seller.customers.show');
    Route::put('/customers/{id}', [\App\Http\Controllers\seller\CustomerController::class, 'update'])->name('seller.customers.update');
    Route::delete('/customers/{id}', [\App\Http\Controllers\seller\CustomerController::class, 'destroy'])->name('seller.customers.destroy');

    Route::get('/categories', [\App\Http\Controllers\seller\CategoryController::class, 'index'])->name('seller.categories');
    Route::post('/categories', [\App\Http\Controllers\seller\CategoryController::class, 'store'])->name('seller.categories.store');
    Route::get('/categories/{id}', [\App\Http\Controllers\seller\CategoryController::class, 'show'])->name('seller.categories.show');
    Route::put('/categories/{id}', [\App\Http\Controllers\seller\CategoryController::class, 'update'])->name('seller.categories.update');
    Route::delete('/categories/{id}', [\App\Http\Controllers\seller\CategoryController::class, 'destroy'])->name('seller.categories.destroy');

    Route::redirect('/settings', '/seller/settings/header');
    Route::get('/settings/header', [\App\Http\Controllers\Admin\SiteSettingController::class, 'headerSettings'])->name('seller.settings.header');
    Route::post('/settings/header', [\App\Http\Controllers\Admin\SiteSettingController::class, 'updateHeaderSettings'])->name('seller.settings.header.update');
    Route::get('/settings/footer', [\App\Http\Controllers\Admin\SiteSettingController::class, 'footerSettings'])->name('seller.settings.footer');
    Route::post('/settings/footer', [\App\Http\Controllers\Admin\SiteSettingController::class, 'updateFooterSettings'])->name('seller.settings.footer.update');
    Route::get('/settings/orders', [\App\Http\Controllers\Admin\SiteSettingController::class, 'orderSettings'])->name('seller.settings.orders');
    Route::post('/settings/orders', [\App\Http\Controllers\Admin\SiteSettingController::class, 'updateOrderSettings'])->name('seller.settings.orders.update');
    Route::get('/profile', [\App\Http\Controllers\seller\ProfileController::class, 'index'])->name('seller.profile');
    Route::put('/profile', [\App\Http\Controllers\seller\ProfileController::class, 'update'])->name('seller.profile.update');


    Route::get('/notifications/{id}', [\App\Http\Controllers\seller\NotificationController::class, 'show'])->name('seller.notifications.show');
    Route::patch('/notifications/{id}/read', [\App\Http\Controllers\seller\NotificationController::class, 'markAsRead'])->name('seller.notifications.markAsRead');
    Route::patch('/notifications/mark-all-read', [\App\Http\Controllers\seller\NotificationController::class, 'markAllAsRead'])->name('seller.notifications.markAllAsRead');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\seller\NotificationController::class, 'destroy'])->name('seller.notifications.destroy');
});

// Seller notification listing and counters (protected)
Route::prefix('seller')->middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/notifications/unread-count', [\App\Http\Controllers\seller\NotificationController::class, 'getUnreadCount'])->name('seller.notifications.unreadCount');
    Route::get('/notifications', [\App\Http\Controllers\seller\NotificationController::class, 'index'])->name('seller.notifications.index');
});
