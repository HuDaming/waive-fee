<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'PagesController@index')->name('root');

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('orders/create', 'OrdersController@create')->name('orders.create');
    Route::post('orders', 'OrdersController@store')->name('orders.store');
});

