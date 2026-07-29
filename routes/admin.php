<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\seller\ProfileController as SellerProfileController;
use App\Http\Controllers\seller\CustomerController as SellerCustomerController;
use App\Http\Controllers\Admin\SellerSettingPermissionController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function() {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/settings/header', [SiteSettingController::class, 'headerSettings'])->name('admin.settings.header');
    Route::post('/settings/header', [SiteSettingController::class, 'updateHeaderSettings'])->name('admin.settings.header.update');

    Route::get('/settings/footer', [SiteSettingController::class, 'footerSettings'])->name('admin.settings.footer');
    Route::post('/settings/footer', [SiteSettingController::class, 'updateFooterSettings'])->name('admin.settings.footer.update');
    Route::get('/settings/mail', [SiteSettingController::class, 'mailSettings'])->name('admin.settings.mail');
    Route::post('/settings/mail', [SiteSettingController::class, 'updateMailSettings'])->name('admin.settings.mail.update');

    Route::get('/settings/orders', [SiteSettingController::class, 'orderSettings'])->name('admin.settings.orders');
    Route::post('/settings/orders', [SiteSettingController::class, 'updateOrderSettings'])->name('admin.settings.orders.update');

    Route::get('/settings/seller-permissions', [SellerSettingPermissionController::class, 'index'])->name('admin.settings.seller-permissions');
    Route::put('/settings/seller-permissions', [SellerSettingPermissionController::class, 'bulkUpdate'])->name('admin.settings.seller-permissions.bulk-update');
    Route::post('/settings/seller-permissions', [SellerSettingPermissionController::class, 'store'])->name('admin.settings.seller-permissions.store');
    Route::put('/settings/seller-permissions/{id}', [SellerSettingPermissionController::class, 'update'])->name('admin.settings.seller-permissions.update');
    Route::delete('/settings/seller-permissions/{id}', [SellerSettingPermissionController::class, 'destroy'])->name('admin.settings.seller-permissions.destroy');

    Route::get('/profile', [SellerProfileController::class, 'index'])->name('admin.profile');
    Route::put('/profile', [SellerProfileController::class, 'update'])->name('admin.profile.update');

    Route::get('/customers', [SellerCustomerController::class, 'index'])->name('admin.customers');
    Route::post('/customers', [SellerCustomerController::class, 'store'])->name('admin.customers.store');
    Route::get('/customers/{id}', [SellerCustomerController::class, 'show'])->name('admin.customers.show');
    Route::put('/customers/{id}', [SellerCustomerController::class, 'update'])->name('admin.customers.update');
    Route::delete('/customers/{id}', [SellerCustomerController::class, 'destroy'])->name('admin.customers.destroy');
});
