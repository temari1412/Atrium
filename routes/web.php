<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    TopPageController, AuthController, MypageController, ProductsController, 
    ProfileController, UserController, ReviewController, DashboardController, 
    MessageController, OrderController, LikeController, SearchController, FollowController,
    CartController, StripeWebhookController, ContactController 
};
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContactController as AdminContactController; 

// トップページ
Route::get('/', [TopPageController::class, 'index'])->name('top');

// Stripe Webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// 認証・ログイン関連
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/register/completed', [AuthController::class, 'showCompleted'])->name('register.completed');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// パスワードリセット関連
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');

// 管理者ログイン
Route::get('admin/login', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('admin/login', [AdminController::class, 'login'])->name('admin.login.submit');

// 管理者専用ルート（上部：ホームやユーザー管理など）
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/home', [AdminController::class, 'home'])->name('home');
    Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
    
    Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');
    Route::post('/users/{id}/toggle-suspend', [AdminController::class, 'toggleSuspend'])->name('users.toggleSuspend');

    Route::get('/reviews', [AdminController::class, 'reviewsIndex'])->name('reviews.index');
    Route::delete('/reviews/{id}', [AdminController::class, 'reviewDestroy'])->name('reviews.destroy');
});

//  認証不要
Route::get('/products/{id}', [ProductsController::class, 'show'])->name('products.show')->where('id', '[0-9]+');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');

// 認証が必要なルート
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage');
    Route::get('/orders/history', [OrderController::class, 'history'])->name('orders.history');
    
    // フォロー関連
    Route::get('/my/following', [FollowController::class, 'followingIndex'])->name('following');
    Route::post('/users/{user}/follow', [FollowController::class, 'toggle'])->name('users.follow');    

    // 商品管理
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/create', [ProductsController::class, 'edit'])->name('create');
        Route::get('/{id}/edit', [ProductsController::class, 'edit'])->name('edit')->where('id', '[0-9]+');
        
        Route::post('/', [ProductsController::class, 'store'])->name('store');
        Route::put('/{id}', [ProductsController::class, 'updateProduct'])->name('update')->where('id', '[0-9]+');
        
        Route::post('/preview', [ProductsController::class, 'preview'])->name('preview'); 
        Route::get('/preview/view', [ProductsController::class, 'showPreview'])->name('preview.view'); 
        Route::post('/publish', [ProductsController::class, 'publish'])->name('publish'); 

        Route::delete('/{id}', [ProductsController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
    });

    // レビュー関連
    Route::prefix('products/{id}/reviews')->name('reviews.')->group(function () {
        Route::get('/create', [ReviewController::class, 'create'])->name('create');
        Route::post('/', [ReviewController::class, 'store'])->name('store');
        Route::get('/{review}/edit', [ReviewController::class, 'edit'])->name('edit');
        Route::patch('/{review}', [ReviewController::class, 'update'])->name('update');
    });
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/orders/{order}/reviews/batch', [ReviewController::class, 'storeBatch'])->name('reviews.storeBatch');

    // その他機能
    Route::post('/products/{id}/like', [LikeController::class, 'toggle'])->name('likes.toggle');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}/read', [MessageController::class, 'read'])->name('messages.read');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');

    // カート関連
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'store'])->name('cart.add');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::put('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');

    // 支払い完了関連
    Route::post('/checkout/{product}', [OrderController::class, 'checkout'])->name('checkout');
    Route::get('/checkout/success/{product}', [OrderController::class, 'success'])->name('checkout.success');
    Route::post('/cart/checkout', [OrderController::class, 'cartCheckout'])->name('cart.checkout');
    Route::get('/cart/checkout/success', [OrderController::class, 'cartSuccess'])->name('cart.checkout.success');
});

// 管理者専用
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/orders', [AdminController::class, 'ordersIndex'])->name('orders.index');
    Route::post('/orders/export', [AdminController::class, 'exportOrdersCsv'])->name('orders.export');
    Route::get('/orders/{id}', [AdminController::class, 'orderShow'])->name('orders.show');
    Route::patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.updateStatus');
});

// 管理者用お問い合わせ管理
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/contacts', [AdminContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/{contact}', [AdminContactController::class, 'show'])->name('contacts.show');
    Route::patch('/contacts/{id}/status', [AdminContactController::class, 'updateStatus'])->name('contacts.updateStatus'); // ← 追加
});

// メール・問い合わせ
Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

