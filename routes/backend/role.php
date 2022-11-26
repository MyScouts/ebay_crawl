<?php

use App\Domains\Auth\Http\Controllers\Backend\Role\RoleController;
use App\Domains\Auth\Models\Role;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::group([
    'prefix' => 'role',
    'as' => 'role.',
    'middleware' => 'role:' . config('boilerplate.access.role.admin'),
], function () {
    Route::get('/', [RoleController::class, 'index'])
        ->name('index')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.dashboard')
                ->push(__('Role Management'), route('admin.auth.role.index'));
        });

    Route::get('create', [RoleController::class, 'create'])
        ->name('create')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.auth.role.index')
                ->push(__('Create Role'), route('admin.auth.role.create'));
        });

    Route::post('/', [RoleController::class, 'store'])->name('store');

    Route::group(['prefix' => '{role}'], function () {
        Route::get('edit', [RoleController::class, 'edit'])
            ->name('edit')
            ->breadcrumbs(function (Trail $trail, Role $role) {
                $trail->parent('admin.auth.role.index')
                    ->push(__('Editing :role', ['role' => $role->name]), route('admin.auth.role.edit', $role));
            });

        Route::patch('/', [RoleController::class, 'update'])->name('update');
        Route::delete('/', [RoleController::class, 'destroy'])->name('destroy');
    });
});
