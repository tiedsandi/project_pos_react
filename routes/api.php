<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::get('/', function () {
//     return response()->json([
//         'success' => true,
//         'message' => 'API POS',
//         'data' => [
//             'version' => '1.0.0',
//             'description' => 'API POS Documentation',
//         ],
//     ]);
// });

Route::group(['prefix' => 'v1'], function () {
    // Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::apiResource('products', ProductController::class);
    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refreshToken']);

        Route::apiResource('users', UserController::class);
        Route::apiResource('categories', CategoryController::class);
    });
});
