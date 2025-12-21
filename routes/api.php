<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Invoice routes
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
        Route::get('/status/{status}', [InvoiceController::class, 'byStatus']);
        Route::get('/customer/{customerName}', [InvoiceController::class, 'byCustomer']);
        Route::get('/{id}', [InvoiceController::class, 'show']);
        Route::get('/{id}/pdf', [InvoiceController::class, 'viewPdf']);
        Route::get('/{id}/pdf/download', [InvoiceController::class, 'downloadPdf']);
        Route::put('/{id}', [InvoiceController::class, 'update']);
        Route::delete('/{id}', [InvoiceController::class, 'destroy']);
        
        // Payment routes
        Route::get('/{invoiceId}/payments', [PaymentController::class, 'index']);
        Route::post('/{invoiceId}/payments', [PaymentController::class, 'store']);
    });
});
