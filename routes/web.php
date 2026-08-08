<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GmailAuthController;
use App\Http\Controllers\GmailController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReferenceTableController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsappTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ---------------------------------------------------------------------
// Setup Wizard — accessible only until the app has been installed.
// ---------------------------------------------------------------------
Route::prefix('setup')->name('setup.')->group(function () {
    Route::get('/', [SetupController::class, 'index'])->name('index');
    Route::post('/test-connection', [SetupController::class, 'testConnection'])->name('test-connection');
    Route::post('/', [SetupController::class, 'store'])->name('store');
});

// ---------------------------------------------------------------------
// Auth
// ---------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// ---------------------------------------------------------------------
// Application (auth required)
// ---------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gmail Inbox
    Route::prefix('gmail')->name('gmail.')->group(function () {
        Route::get('/', [GmailController::class, 'index'])->name('index');
        Route::patch('/{conversation}/star', [GmailController::class, 'toggleStar'])->name('star');
        Route::get('/{conversation}/toggle-star', [GmailController::class, 'toggleStar'])->name('star.get');
        Route::patch('/{conversation}/folder', [GmailController::class, 'moveFolder'])->name('folder');
        Route::post('/{conversation}/reply', [GmailController::class, 'reply'])->name('reply');
        Route::post('/{conversation}/create-contact', [GmailController::class, 'createContact'])->name('create-contact');
    });

    // Contacts
    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
        Route::post('/', [ContactController::class, 'store'])->name('store');
        Route::get('/export', [ContactController::class, 'export'])->name('export');

        Route::middleware('admin')->group(function () {
            Route::get('/import', [ContactController::class, 'importForm'])->name('import.form');
            Route::post('/import', [ContactController::class, 'import'])->name('import');
            Route::post('/sync-settings', [ContactController::class, 'syncSettingsUpdate'])->name('sync-settings');
            Route::post('/sync-now', [ContactController::class, 'syncNow'])->name('sync-now');
        });

        Route::get('/{contact}', [ContactController::class, 'show'])->name('show');
        Route::put('/{contact}', [ContactController::class, 'update'])->name('update');
        Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('destroy');
        Route::patch('/{contact}/star', [ContactController::class, 'toggleStar'])->name('star');
        Route::patch('/{contact}/archive', [ContactController::class, 'archive'])->name('archive');
        Route::patch('/{contact}/unarchive', [ContactController::class, 'unarchive'])->name('unarchive');
        Route::patch('/{contact}/won', [ContactController::class, 'markWon'])->name('won');
        Route::patch('/{contact}/unwon', [ContactController::class, 'unmarkWon'])->name('unwon');
        Route::get('/{contact}/whatsapp', [ContactController::class, 'whatsapp'])->name('whatsapp');
    });

    // WhatsApp Templates — managed from Settings, admin-only.
    Route::prefix('whatsapp')->name('whatsapp.')->middleware('admin')->group(function () {
        Route::post('/', [WhatsappTemplateController::class, 'store'])->name('store');
        Route::put('/{whatsappTemplate}', [WhatsappTemplateController::class, 'update'])->name('update');
        Route::delete('/{whatsappTemplate}', [WhatsappTemplateController::class, 'destroy'])->name('destroy');
    });

    // Product Master
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        Route::post('/import', [ProductController::class, 'import'])->name('import');
        Route::get('/export', [ProductController::class, 'export'])->name('export');
    });

    // Standard reference tables (Product Master popup)
    Route::prefix('reference-tables')->name('reference-tables.')->group(function () {
        Route::post('/', [ReferenceTableController::class, 'store'])->name('store');
        Route::put('/{referenceTable}', [ReferenceTableController::class, 'update'])->name('update');
        Route::delete('/{referenceTable}', [ReferenceTableController::class, 'destroy'])->name('destroy');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile');
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('password');
        Route::get('/gmail/callback', [GmailAuthController::class, 'callback'])->name('gmail.callback');

        Route::middleware('admin')->group(function () {
            Route::post('/gmail/credentials', [GmailAuthController::class, 'saveCredentials'])->name('gmail.credentials');
            Route::get('/gmail/connect', [GmailAuthController::class, 'redirect'])->name('gmail.connect');
            Route::post('/gmail/disconnect', [GmailAuthController::class, 'disconnect'])->name('gmail.disconnect');
            Route::post('/gmail/sync-now', [GmailAuthController::class, 'syncNow'])->name('gmail.sync-now');
            Route::put('/whatsapp', [SettingsController::class, 'updateWhatsapp'])->name('whatsapp');
            Route::put('/preferences', [SettingsController::class, 'updatePreferences'])->name('preferences');
        });
    });

    // Team account management — admin only
    Route::middleware('admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
    });
});
