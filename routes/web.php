<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Print\GoodsReceiptPrintController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/print/goods-receipt/{receipt}', GoodsReceiptPrintController::class)
        ->name('goods-receipt.print');
});
