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

Route::get('/chucmung20t10', function () {
    return view('code20t10.index');
});
Route::get('/chucmung20t10/flower', function () {
    return view('code20t10.index2');
});
