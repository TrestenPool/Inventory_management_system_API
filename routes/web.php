<?php

use Illuminate\Support\Facades\Route;
require __DIR__.'/auth.php';

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

/* GLOBAL PATTERNS */
Route::pattern('id', '[0-9]+');

/* Index page */
Route::get('/', function () {
    return view('devices.index');
})->middleware(['auth'])->name('indexPage');


/* Dashboard */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');


