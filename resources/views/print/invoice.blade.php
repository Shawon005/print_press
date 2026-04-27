<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Invoice {{ $invoice->invoice_number }}</title><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}table{width:100%;border-collapse:collapse;margin-top:10px}th,td{border:1px solid #333;padding:6px}th{background:#efefef}.right{text-align:right}</style></head>
<body>
<h2>Invoice</h2>
<p><strong>Invoice:</strong> {{ $invoice->invoice_number }} | <strong>Date:</strong> {{ optional($invoice->invoice_date)->format('d M Y') }}</p>
<p><strong>Customer:</strong> {{ $invoice->customer?->company_name }}</p>
<table>
<tr><th>Subtotal</th><th>Discount</th><th>Tax</th><th>Total</th><th>Paid</th><th>Due</th></tr>
<tr>
<td class="right">{{ number_format((float)$invoice->subtotal,2) }}</td>
<td class="right">{{ number_format((float)$invoice->discount,2) }}</td>
<td class="right">{{ number_format((float)$invoice->tax,2) }}</td>
<td class="right">{{ number_format((float)$invoice->total,2) }}</td>
<td class="right">{{ number_format((float)$invoice->paid_amount,2) }}</td>
<td class="right">{{ number_format((float)$invoice->due_amount,2) }}</td>
</tr>
</table>
</body>
</html>
