<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\PrintAreaController;
use App\Http\Controllers\Admin\ProductColorController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductOptionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ShippingRateController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderFileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\OrderActionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

// ---- Publik ----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/produk', [CatalogController::class, 'index'])->name('products.index');
Route::get('/produk/{product}', [CatalogController::class, 'show'])->name('products.show');
Route::get('/portofolio', [GalleryController::class, 'index'])->name('portfolios.index');
Route::get('/testimoni', [ReviewController::class, 'index'])->name('testimonials.index');
Route::get('/kontak', [ContactController::class, 'show'])->name('contact');

// ---- Tamu ----
Route::middleware('guest')->group(function () {
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/lupa-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

// ---- Sudah login ----
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Mockup per warna (disk privat), untuk pengguna login.
    Route::get('/media/mockups/{color}', [MediaController::class, 'mockup'])->name('media.mockup');

    // Detail pesanan: pemilik atau admin (dicek di controller; selain itu 404).
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/pesanan/{order}/nota', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/pesanan/{order}/pembayaran', [PaymentController::class, 'store'])
        ->middleware('throttle:12,1')
        ->name('payments.store');
    Route::post('/pesanan/{order}/diterima', [OrderController::class, 'confirmReceived'])->name('orders.received');
    Route::get('/pesanan/{order}/desain/{design}', [OrderFileController::class, 'design'])->name('files.design');
    Route::get('/pesanan/{order}/bukti/{payment}', [OrderFileController::class, 'proof'])->name('files.proof');
});

// ---- Pemesanan: wajib email terverifikasi (BR-11) ----
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pesan/{product}', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/pesan/{product}', [OrderController::class, 'store'])
        ->middleware('throttle:12,1')
        ->name('orders.store');
});

// ---- Admin ----
Route::middleware(['auth', EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

        Route::post('orders/{order}/payments/{payment}/verify', [OrderActionController::class, 'verifyPayment'])->name('orders.payments.verify');
        Route::post('orders/{order}/payments/{payment}/reject', [OrderActionController::class, 'rejectPayment'])->name('orders.payments.reject');
        Route::post('orders/{order}/lunas', [OrderActionController::class, 'markPaid'])->name('orders.paid');
        Route::post('orders/{order}/status', [OrderActionController::class, 'updateStatus'])->name('orders.status');
        Route::post('orders/{order}/revisi', [OrderActionController::class, 'updateRevision'])->name('orders.revision');
        Route::post('orders/{order}/desain-final', [OrderActionController::class, 'storeFinalDesign'])->name('orders.finalDesign.store');
        Route::delete('orders/{order}/desain-final/{design}', [OrderActionController::class, 'destroyFinalDesign'])->name('orders.finalDesign.destroy');
        Route::post('orders/{order}/batal', [OrderActionController::class, 'cancel'])->name('orders.cancel');

        Route::resource('categories', CategoryController::class)->except(['show', 'create']);

        Route::resource('products', ProductController::class)->except(['show']);
        Route::resource('products.options', ProductOptionController::class)->shallow()->only(['store', 'update', 'destroy']);
        Route::resource('products.colors', ProductColorController::class)->shallow()->only(['store', 'update', 'destroy']);
        Route::resource('products.areas', PrintAreaController::class)->shallow()->only(['store', 'update', 'destroy']);

        Route::resource('portfolios', PortfolioController::class)->except(['show']);

        Route::patch('testimonials/{testimonial}/publish', [TestimonialController::class, 'togglePublish'])
            ->name('testimonials.publish');
        Route::resource('testimonials', TestimonialController::class)->except(['show', 'create']);

        Route::resource('couriers', CourierController::class)->except(['show', 'create']);
        Route::resource('couriers.rates', ShippingRateController::class)->shallow()->only(['store', 'update', 'destroy']);

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
    });