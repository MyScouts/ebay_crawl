<?php

use App\Http\Controllers\BackEnd\SettingController;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

Route::group([
    'as'            => 'setting.',
    'prefix'        => 'setting',
    'middleware'    => 'role:' . config('boilerplate.access.role.admin'),
], function () {
    Route::get('', [SettingController::class, 'index'])->name('index')
        ->breadcrumbs(function (Trail $trail) {
            $trail->parent('admin.dashboard')
                ->push(__('Settings'), route('admin.setting.index'));
        });


    Route::patch('update', [SettingController::class, 'update'])->name('update');
});
