<?php

use App\Domains\Auth\Http\Controllers\Backend\User\UserPasswordController;
use App\Domains\Auth\Models\User;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

// All route names are prefixed with 'admin.auth'.
Route::group([
    'prefix' => 'auth',
    'as' => 'auth.',
    // 'middleware' => config('boilerplate.access.middleware.confirm'),
], function () {

    Route::get('password/change', [UserPasswordController::class, 'edit'])
        ->name('change-password')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.dashboard')
                ->push(__('Change Password'), route('admin.auth.change-password'));
        });

    Route::patch('password/change', [UserPasswordController::class, 'update'])
        ->name('change-password.update');

    include "user.php";
    include "role.php";
});
