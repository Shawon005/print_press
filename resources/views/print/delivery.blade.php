<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Delivery {{ $delivery->delivery_number }}</title><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}table{width:100%;border-collapse:collapse;margin-top:10px}th,td{border:1px solid #333;padding:6px}th{background:#efefef}</style></head>
<body>
<h2>Delivery Challan</h2>
<p><strong>Delivery No:</strong> {{ $delivery->delivery_number }}</p>
<p><strong>Job Order:</strong> {{ $delivery->jobOrder?->job_number ?? ('ID: ' . $delivery->order_id) }} | <strong>Customer:</strong> {{ $delivery->jobOrder?->customer?->company_name }}</p>
<p><strong>Date:</strong> {{ optional($delivery->delivery_date)->format('d M Y') }} | <strong>Status:</strong> {{ str($delivery->status)->headline() }}</p>
<table>
<tr><th>Vehicle</th><th>Transport Cost</th><th>Received By</th></tr>
<tr><td>{{ $delivery->vehicle_no }}</td><td>{{ number_format((float)$delivery->transport_cost,2) }}</td><td>{{ $delivery->received_by }}</td></tr>
</table>
<p><strong>Notes:</strong> {{ $delivery->notes }}</p>
</body>
</html>
