<?php

use Illuminate\Support\Facades\Route;

// Customer Routes (all require authentication)
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/customer/dashboard', [App\Http\Controllers\customer\DashboardController::class, 'index'])->name('customer.dashboard');
    Route::get('/customer/orders', [App\Http\Controllers\CustomerController::class, 'orders'])->name('customer.orders');
    Route::post('/customer/orders/{order}/pay', [App\Http\Controllers\CustomerController::class, 'payOrder'])->name('customer.orders.pay');
    Route::patch('/customer/orders/{order}/cancel', [App\Http\Controllers\CustomerController::class, 'cancelOrder'])->name('customer.orders.cancel');
    Route::patch('/customer/orders/{order}/items/{orderItem}', [App\Http\Controllers\CustomerController::class, 'updateOrderItem'])->name('customer.orders.items.update');
    Route::delete('/customer/orders/{order}/items/{orderItem}', [App\Http\Controllers\CustomerController::class, 'removeOrderItem'])->name('customer.orders.items.remove');
    Route::get('/customer/order/{order}', [App\Http\Controllers\CustomerController::class, 'orderDetails'])->name('customer.order.details');
    Route::get('/customer/profile', [App\Http\Controllers\customer\ProfileController::class, 'index'])->name('customer.profile');
    Route::patch('/customer/profile', [App\Http\Controllers\customer\ProfileController::class, 'update'])->name('customer.profile.update');
    Route::post('/customer/profile/photo', [App\Http\Controllers\customer\ProfileController::class, 'updatePhoto'])->name('customer.profile.photo');
    Route::patch('/customer/profile/password', [App\Http\Controllers\customer\ProfileController::class, 'changePassword'])->name('customer.profile.password');
    Route::get('/customer/addresses', [App\Http\Controllers\CustomerController::class, 'addresses'])->name('customer.addresses');
});
