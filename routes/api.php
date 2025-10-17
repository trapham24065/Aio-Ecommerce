<?php

use App\Http\Controllers\Auth\ApiAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// == CÁC ROUTE CÔNG KHAI (PUBLIC) ==
// Route này phải nằm BÊN NGOÀI group để bất kỳ ai cũng có thể truy cập
Route::post('/login', [ApiAuthController::class, 'login']);

// == CÁC ROUTE CẦN BẢO VỆ (PROTECTED) ==
// Bất kỳ route nào nằm BÊN TRONG group này đều BẮT BUỘC phải có token hợp lệ
Route::middleware('auth:sanctum')->group(function () {
    // Route logout yêu cầu người dùng phải đăng nhập trước
    Route::post('/logout', [ApiAuthController::class, 'logout']);

    // Route này dùng để kiểm tra thông tin user hiện tại
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
