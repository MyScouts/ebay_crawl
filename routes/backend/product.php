<?php

use App\Http\Controllers\Backend\ProductController;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::group([
    'prefix'    => 'products',
    'as'        => 'products.',
    'middleware' => 'role:publish-product|' . config('boilerplate.access.role.admin')
], function () {
    Route::get('', [ProductController::class, 'index'])
        ->name('list')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.dashboard')
                ->push(__('Products'), route('admin.products.list'));
        });

    Route::patch('{productId}/publish',     [ProductController::class, 'publishDo'])->name('publishDo');
    Route::patch('{productId}/unPublish',   [ProductController::class, 'unPublishProduct'])->name('unPublishProduct');
    Route::get('/next',                     [ProductController::class, 'nextProduct'])->name('nextProduct');
});
