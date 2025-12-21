<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #2563eb; }
        .info-section { margin-bottom: 20px; }
        .info-section table { width: 100%; }
        .info-section td { padding: 5px; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .invoice-table th { background-color: #2563eb; color: white; padding: 10px; text-align: left; }
        .invoice-table td { padding: 8px; border-bottom: 1px solid #ddd; }
        .totals { margin-top: 20px; text-align: right; }
        .totals table { margin-left: auto; width: 300px; }
        .totals td { padding: 5px; }
        .total-row { font-weight: bold; font-size: 14px; border-top: 2px solid #333; }
        .status { display: inline-block; padding: 5px 10px; border-radius: 3px; font-weight: bold; }
        .status-draft { background-color: #e5e7eb; color: #374151; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-overdue { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p>{{ $invoice->invoice_number }}</p>
    </div>

    <div class="info-section">
        <table>
            <tr>
                <td style="width: 50%;">
                    <strong>Bill To:</strong><br>
                    {{ $invoice->customer_name }}<br>
                    @if($invoice->customer_address)
                        {{ $invoice->customer_address }}<br>
                    @endif
                    @if($invoice->customer_email)
                        {{ $invoice->customer_email }}
                    @endif
                </td>
                <td style="width: 50%; text-align: right;">
                    <strong>Invoice Date:</strong> {{ $invoice->issue_date->format('M d, Y') }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}<br>
                    <strong>Status:</strong> 
                    <span class="status status-{{ strtolower($invoice->status) }}">
                        {{ $invoice->status }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: right;">Quantity</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Tax Rate</th>
                <th style="text-align: right;">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right;">{{ number_format($item->tax_rate, 2) }}%</td>
                <td style="text-align: right;">${{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td style="text-align: right;">${{ number_format($invoice->sub_total, 2) }}</td>
            </tr>
            @if($invoice->tax_amount > 0)
            <tr>
                <td>Tax:</td>
                <td style="text-align: right;">${{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            @endif
            @if($invoice->discount_amount > 0)
            <tr>
                <td>Discount:</td>
                <td style="text-align: right;">-${{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Total:</td>
                <td style="text-align: right;">${{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($invoice->notes)
    <div style="margin-top: 30px;">
        <strong>Notes:</strong><br>
        {{ $invoice->notes }}
    </div>
    @endif
</body>
</html>
