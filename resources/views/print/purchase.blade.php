<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Purchase {{ $purchase->po_number }}</title><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}table{width:100%;border-collapse:collapse;margin-top:10px}th,td{border:1px solid #333;padding:6px}th{background:#efefef}.right{text-align:right}</style></head>
<body>
<h2>Purchase Order</h2>
<p><strong>PO:</strong> {{ $purchase->po_number }} | <strong>Supplier:</strong> {{ $purchase->supplier?->company_name }}</p>
<p><strong>Date:</strong> {{ optional($purchase->order_date)->format('d M Y') }} | <strong>Status:</strong> {{ str($purchase->status)->headline() }}</p>
<table>
<tr><th>Subtotal</th><th>Discount</th><th>Tax</th><th>Total</th><th>Paid</th><th>Due</th></tr>
<tr>
<td class="right">{{ number_format((float)$purchase->subtotal,2) }}</td>
<td class="right">{{ number_format((float)$purchase->discount,2) }}</td>
<td class="right">{{ number_format((float)$purchase->tax,2) }}</td>
<td class="right">{{ number_format((float)$purchase->total,2) }}</td>
<td class="right">{{ number_format((float)$purchase->paid_amount,2) }}</td>
<td class="right">{{ number_format((float)$purchase->due_amount,2) }}</td>
</tr>
</table>
</body>
</html>
