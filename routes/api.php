<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//// If need custom service:
//
//// Create new image
//Route::post('image', function() {
//    return 123;
//})->name('image.create');
//
//// Return image
//Route::get('image/{id}', function() {
//    return 123;
//})->where('id', '[0-9]+')->name('image.get');

Route::apiResource('image', 'Api\ImageController');