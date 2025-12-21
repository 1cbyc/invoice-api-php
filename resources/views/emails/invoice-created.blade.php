<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .invoice-details {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .invoice-details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .total {
            font-size: 1.2em;
            color: #4CAF50;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 0.9em;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-draft { background-color: #ffeaa7; color: #2d3436; }
        .status-sent { background-color: #74b9ff; color: #2d3436; }
        .status-paid { background-color: #55efc4; color: #2d3436; }
        .status-overdue { background-color: #ff7675; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>New Invoice Created</h1>
    </div>
    
    <div class="content">
        <p>Hello,</p>
        <p>A new invoice has been created for <strong>{{ $invoice->customer_name }}</strong>.</p>
        
        <div class="invoice-details">
            <table>
                <tr>
                    <td>Invoice Number:</td>
                    <td>#{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>Customer:</td>
                    <td>{{ $invoice->customer_name }}</td>
                </tr>
                @if($invoice->customer_email)
                <tr>
                    <td>Email:</td>
                    <td>{{ $invoice->customer_email }}</td>
                </tr>
                @endif
                <tr>
                    <td>Invoice Date:</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td>Due Date:</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td>
                        <span class="status status-{{ strtolower($invoice->status) }}">
                            {{ $invoice->status }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Total Amount:</td>
                    <td class="total">${{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($invoice->items && count($invoice->items) > 0)
        <h3>Invoice Items</h3>
        <div class="invoice-details">
            <table>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align: right;">
                        {{ $item->quantity }} x ${{ number_format($item->unit_price, 2) }} = 
                        ${{ number_format($item->quantity * $item->unit_price, 2) }}
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
        @endif

        @if($invoice->notes)
        <p><strong>Notes:</strong><br>{{ $invoice->notes }}</p>
        @endif
        
        <p>You can view or download this invoice from your dashboard.</p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} Invoice API. All rights reserved.</p>
    </div>
</body>
</html>
