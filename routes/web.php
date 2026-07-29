<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ClickPesaController;
use App\Models\Notification;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('shop');
});


Route::get('/about', [\App\Http\Controllers\PageController::class, 'about'])->name('about');
Route::get('/contact', [\App\Http\Controllers\PageController::class, 'contact'])->name('contact');



// Shop Routes
Route::get('/categories', [\App\Http\Controllers\ShopController::class, 'categories'])->name('categories');
Route::get('/shop', [\App\Http\Controllers\ShopController::class, 'index'])->name('shop');
Route::get('/shop/{public_id}/{slug}', [\App\Http\Controllers\ShopController::class, 'show'])->name('shop.show');
Route::post('/shop/{public_id}/{slug}/view-activity', [\App\Http\Controllers\ShopController::class, 'trackViewActivity'])->name('shop.view.activity');
Route::post('/shop/{public_id}/{slug}/rate', [\App\Http\Controllers\ShopController::class, 'storeRating'])->middleware(['auth', 'verified', 'role:customer'])->name('shop.rate');
Route::get('/category/{slug}', [\App\Http\Controllers\ShopController::class, 'category'])->name('category.show');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'Verification link sent.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::post('/clickpesa/webhook', [ClickPesaController::class, 'webhook'])->name('clickpesa.webhook');
Route::match(['get', 'post'], '/clickpesa/callback', [ClickPesaController::class, 'callback'])->name('clickpesa.callback');

require __DIR__.'/shop.php';
require __DIR__.'/seller.php';
require __DIR__.'/customer.php';
require __DIR__.'/admin.php';

// Public notification endpoints for AJAX polling (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/api/notifications/unread-count', function () {
        $query = Notification::query()->where(function (Builder $query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });

        // Apply role-based filtering like in the Blade template
        if (Auth::user()->role !== 'seller') {
            $query->where('user_id', Auth::id());
        }

        $count = $query->unread()->count();

        return response()->json(['count' => $count]);
    });

    Route::get('/api/notifications/recent', function () {
        $query = Notification::query()->where(function (Builder $query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });

        // Apply role-based filtering like in the Blade template
        if (Auth::user()->role !== 'seller') {
            $query->where('user_id', Auth::id());
        }

        $notifications = $query->latest()->take(3)->get()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
            ];
        });

        return response()->json(['notifications' => $notifications]);
    });
});
