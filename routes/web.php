<?php

use App\Http\Controllers\Voyager\ProductController;
use Illuminate\Support\Facades\Route;
use TCG\Voyager\Facades\Voyager;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/admin/login');
});



Route::group(['prefix' => 'admin'], function () {
    // Route::resource('/admin/products', [App\Http\Controllers\Voyager\ProductController::class]);
    Voyager::routes();
    Route::post('admin/transactions', [App\Http\Controllers\Voyager\TransactionController::class, 'store'])->name('store');
    Route::get('/transaction/tampil',[App\Http\Controllers\Voyager\TransactionController::class, 'tampil'])->name('otomatis');
    Route::get('/transaction/cetak/{id}',[App\Http\Controllers\Voyager\TransactionController::class, 'cetak'])->name('cetak');
});
