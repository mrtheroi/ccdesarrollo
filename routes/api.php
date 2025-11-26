<?php

use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FinanzasApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::post('/contact', [ContactController::class, 'submitContact'])
        ->name('contact.submit');



    Route::prefix('finanzas')->group(function () {

        // Estado de resultados (varios meses)
        Route::get('/estado-resultados', [FinanzasApiController::class, 'estadoResultados']);

        // Estado de resultados por mes específico
        Route::get('/estado-resultados/{year}/{month}', [FinanzasApiController::class, 'estadoResultadosMes']);

        Route::get('/estado-resultados-anual', [FinanzasApiController::class, 'estadoResultadosAnual']);

        // Ventas por cliente
        Route::get('/ventas-clientes', [FinanzasApiController::class, 'ventasClientes']);

        // Gastos por categoría
        Route::get('/gastos-categoria', [FinanzasApiController::class, 'gastosCategoria']);

        // Cartera: resumen por tramo
        Route::get('/cartera-resumen', [FinanzasApiController::class, 'carteraResumen']);

        // Cartera: detalle de facturas pendientes
        Route::get('/cartera-detalle', [FinanzasApiController::class, 'carteraDetalle']);
    });
});
