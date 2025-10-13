<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoodsReceiptPrintController extends Controller
{

    /**
     * Handle the incoming request.
     */
    public function __invoke(GoodsReceipt $receipt): View
    {
        // Eager load các relationship để tối ưu query
        $receipt->load('items.productVariant.product', 'warehouse', 'supplier', 'user');

        return view('prints.goods-receipt', compact('receipt'));
    }

}
