<?php

use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\validateID;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/******** DEVICE ROUTES *********/
Route::get('/device', 
  [DeviceController::class, 'index'])
  ->name('index_device');

Route::post('/device',
  [DeviceController::class, 'store'])
  ->name('insert_device');

Route::get('/device/{device_id}',
  [DeviceController::class, 'show'])
  ->middleware('validateID')
  ->name('show_device');
  
Route::put('/device/{device_id}',
  [DeviceController::class, 'update'])
  ->middleware('validateID')
  ->name('update_device');

Route::delete('/device/{device_id}',
  [DeviceController::class, 'destroy'])
  ->middleware('validateID')
  ->name('delete_device');


/******** FILE ROUTES *********/
// show all files for a device
Route::get('device/{device_id}/file', 
  [DeviceController::class, 'showFiles'])
  ->middleware('validateID');
  
// Insert new file for the device
Route::post('device/{device_id}/file', 
  [DeviceController::class, 'addFile'])
  ->middleware('validateID');

// show a specific file for the device
Route::get('device/{device_id}/file/{file_id}', 
  [DeviceController::class, 'showFile'])
  ->middleware('validateID');

// Update an existing file for the device
Route::post('device/{device_id}/file/{file_id}', 
  [DeviceController::class, 'updateFile'])
  ->middleware('validateID');

// delete a specific file for the device
Route::delete('device/{device_id}/file/{file_id}', 
  [DeviceController::class, 'deleteFile'])
  ->middleware('validateID');


/******** MANUFACTURER ROUTES *********/
Route::get('/manufacturer',
  [ManufacturerController::class, 'index'])
  ->name('index_manufacturer');

Route::post('/manufacturer',
  [ManufacturerController::class, 'store'])
  ->name('insert_manufacturer');

Route::get('/manufacturer/{manufacturer_id}',
  [ManufacturerController::class, 'show'])
  ->middleware('validateID')
  ->name('show_manufacturer');

Route::put('/manufacturer/{manufacturer_id}',
  [ManufacturerController::class, 'update'])
  ->middleware('validateID')
  ->name('update_manufacturer');

Route::delete('/manufacturer/{manufacturer_id}',
  [ManufacturerController::class, 'destroy'])
  ->middleware('validateID')
  ->name('delete_manufacturer');


/******** PRODUCT ROUTES *********/
Route::get('/product',
  [ProductController::class, 'index'])
  ->name('index_product');

Route::post('/product',
  [ProductController::class, 'store'])
  ->name('insert_product');

Route::get('/product/{product_id}',
  [ProductController::class, 'show'])
  ->middleware('validateID')
  ->name('show_product');

Route::put('/product/{product_id}', 
  [ProductController::class, 'update'])
  ->middleware('validateID')
  ->name('update_product');

Route::delete('/product/{product_id}', 
  [ProductController::class, 'destroy'])
  ->middleware('validateID')
  ->name('delete_product');


/******** FILE ROUTES *********/
// file test routes
Route::get('/file', function(Request $request){
  return 'file get route';
});

// fallback route
// Route::fallback(function() {
//   return response()->json([
//     'Status' => 'Error',
//     'Message' => 'The route you requested does not exists...',
//     'Data' => ''
//   ], 404);
// });

// upload file
Route::post('/file', [FileController::class, 'upload']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});