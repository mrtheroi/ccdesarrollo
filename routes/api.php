<?php

use App\Http\Controllers\Api\V1\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/contact', [ContactController::class, 'submitContact'])
        ->name('contact.submit');
});
