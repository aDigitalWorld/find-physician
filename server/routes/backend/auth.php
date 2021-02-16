<?php

use App\Http\Controllers\Backend\Auth\Role\RoleController;
use App\Http\Controllers\Backend\Auth\User\UserController;
use App\Http\Controllers\Backend\Auth\User\UserSocialController;
use App\Http\Controllers\Backend\Auth\User\UserStatusController;
use App\Http\Controllers\Backend\Auth\User\UserSessionController;
use App\Http\Controllers\Backend\Auth\User\UserPasswordController;
use App\Http\Controllers\Backend\Auth\User\UserConfirmationController;
use App\Http\Controllers\Backend\Accounts\AccountsController;
use App\Http\Controllers\Backend\Accounts\AccountsStatusController;
// All route names are prefixed with 'admin.auth'.

// Role Management
Route::group([
    'namespace' => 'Accounts',
    'middleware' => 'role:client|administrator',
], function () {
    Route::get('accounts', [AccountsController::class, 'index'])->name('accounts.index');
    Route::get('accounts/start-sync', [AccountsController::class, 'startSync'])->name('accounts.start_sync');
    // Route::post('accounts/accountsList','AccountsController@accountsList')->name('accounts.list');
    Route::post('accounts/accountsList', [AccountsController::class, 'accountsList'])->name('accounts.list');
    Route::get('accounts/create', [AccountsController::class, 'create'])->name('accounts.create');
    Route::post('accounts', [AccountsController::class, 'store'])->name('accounts.store');


    Route::get('accounts/deactivated', [AccountsStatusController::class, 'getDeactivated'])->name('accounts.deactivated');
    Route::get('accounts/deleted', [AccountsStatusController::class, 'getDeleted'])->name('account.deleted');
    Route::group(['prefix' => 'accounts/{account}'], function () {

        Route::get('/', [AccountsController::class, 'show'])->name('account.show');
        Route::get('edit', [AccountsController::class, 'edit'])->name('account.edit');
        Route::patch('/', [AccountsController::class, 'update'])->name('account.update');
        Route::delete('/', [AccountsController::class, 'destroy'])->name('account.destroy');

        // Deleted
        Route::get('delete', [AccountsStatusController::class, 'delete'])->name('account.delete-permanently');
        Route::get('restore', [AccountsStatusController::class, 'restore'])->name('account.restore');

        Route::get('mark/{status}', [AccountsStatusController::class, 'mark'])->name('account.mark')->where(['status' => '[0,1]']);
    });

});

Route::group([
    'prefix' => 'auth',
    'as' => 'auth.',
    'namespace' => 'Auth',
    'middleware' => 'role:'.config('access.users.admin_role'),
], function () {
    // User Management
    Route::group(['namespace' => 'User'], function () {
        // User Status'
        Route::get('user/deactivated', [UserStatusController::class, 'getDeactivated'])->name('user.deactivated');
        Route::get('user/deleted', [UserStatusController::class, 'getDeleted'])->name('user.deleted');

        // User CRUD
        Route::get('user', [UserController::class, 'index'])->name('user.index');
        Route::get('user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('user', [UserController::class, 'store'])->name('user.store');

        // Specific User
        Route::group(['prefix' => 'user/{user}'], function () {
            // User
            Route::get('/', [UserController::class, 'show'])->name('user.show');
            Route::get('edit', [UserController::class, 'edit'])->name('user.edit');
            Route::patch('/', [UserController::class, 'update'])->name('user.update');
            Route::delete('/', [UserController::class, 'destroy'])->name('user.destroy');

            // Account
            Route::get('account/confirm/resend', [UserConfirmationController::class, 'sendConfirmationEmail'])->name('user.account.confirm.resend');

            // Status
            Route::get('mark/{status}', [UserStatusController::class, 'mark'])->name('user.mark')->where(['status' => '[0,1]']);

            // Social
            Route::delete('social/{social}/unlink', [UserSocialController::class, 'unlink'])->name('user.social.unlink');

            // Confirmation
            Route::get('confirm', [UserConfirmationController::class, 'confirm'])->name('user.confirm');
            Route::get('unconfirm', [UserConfirmationController::class, 'unconfirm'])->name('user.unconfirm');

            // Password
            Route::get('password/change', [UserPasswordController::class, 'edit'])->name('user.change-password');
            Route::patch('password/change', [UserPasswordController::class, 'update'])->name('user.change-password.post');

            // Session
            Route::get('clear-session', [UserSessionController::class, 'clearSession'])->name('user.clear-session');

            // Deleted
            Route::get('delete', [UserStatusController::class, 'delete'])->name('user.delete-permanently');
            Route::get('restore', [UserStatusController::class, 'restore'])->name('user.restore');
        });
    });

    // Role Management
    Route::group(['namespace' => 'Role'], function () {
        Route::get('role', [RoleController::class, 'index'])->name('role.index');
        Route::get('role/create', [RoleController::class, 'create'])->name('role.create');
        Route::post('role', [RoleController::class, 'store'])->name('role.store');

        Route::group(['prefix' => 'role/{role}'], function () {
            Route::get('edit', [RoleController::class, 'edit'])->name('role.edit');
            Route::patch('/', [RoleController::class, 'update'])->name('role.update');
            Route::delete('/', [RoleController::class, 'destroy'])->name('role.destroy');
        });
    });

});
