<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Receipt Note: {{ $receipt->code }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .container { width: 90%; margin: 0 auto; }
        .header, .footer-signatures { text-align: center; }
        .header { margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .info-table td { padding: 6px; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th, .items-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; font-weight: bold; }
        .items-table td.center { text-align: center; }
        .items-table td.right { text-align: right; }
        .footer-signatures { margin-top: 60px; display: flex; justify-content: space-around; padding-top: 20px; }
        .signature-block { width: 30%; }
        .signature-block .line { border-bottom: 1px solid #333; margin-top: 40px; }
        @media print {
            body { margin: 0; }
            .container { width: 100%; margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
<div class="container">
    <div class="header">
        <h1>Goods Receipt Note</h1>
        <p>Date Issued: {{ $receipt->receipt_date->format('F j, Y') }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="50%"><strong>Receipt Code:</strong> {{ $receipt->code }}</td>
            <td width="50%"><strong>Warehouse:</strong> {{ $receipt->warehouse->name }}</td>
        </tr>
        <tr>
            <td><strong>Supplier:</strong> {{ $receipt->supplier?->name ?? 'N/A' }}</td>
            <td><strong>Created By:</strong> {{ $receipt->user->name }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
        <tr>
            <th class="center" width="5%">No.</th>
            <th width="25%">SKU</th>
            <th>Product Name</th>
            <th class="right" width="15%">Quantity</th>
        </tr>
        </thead>
        <tbody>
        @foreach($receipt->items as $index => $item)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $item->product_variant_sku }}</td>
                <td>{{ $item->productVariant?->product?->name ?? 'Product not found' }}</td>
                <td class="right">{{ number_format($item->quantity) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="footer-signatures">
        <div class="signature-block">
            <div class="line"></div>
            <strong>Delivered By</strong><br>
            (Signature, Full Name)
        </div>
        <div class="signature-block">
            <div class="line"></div>
            <strong>Received By</strong><br>
            (Signature, Full Name)
        </div>
        <div class="signature-block">
            <div class="line"></div>
            <strong>Prepared By</strong><br>
            (Signature, Full Name)
        </div>
    </div>
</div>
</body>
</html>
