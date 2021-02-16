<?php
// @link https://interworks.com.mk/how-to-integrate-coreui-in-laravel-definitive-guide/
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\Auth\Accounts\AccountsController;

// All route names are prefixed with 'admin.'.
Route::redirect('/', '/admin/dashboard', 301);
Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
//Route::get('accounts', [AccountsController::class, 'index'])->name('accounts');
