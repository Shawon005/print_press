<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Order {{ $order->job_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 12px; }
        h1 { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #efefef; }
    </style>
</head>
<body>
<h1>Job Card</h1>
<p><strong>Job Number:</strong> {{ $order->job_number }}</p>
<p><strong>Job Title:</strong> {{ $order->job_title }}</p>
<p><strong>Customer:</strong> {{ $order->customer?->company_name }}</p>
<p><strong>Status:</strong> {{ str($order->status)->headline() }}</p>
<table>
    <tr><th>Paper</th><th>GSM</th><th>Page Size</th><th>Copies</th><th>Colors</th><th>Printing Style</th></tr>
    <tr>
        <td>{{ $order->paperType?->name }}</td>
        <td>{{ $order->gsm }}</td>
        <td>{{ $order->page_size }}</td>
        <td>{{ number_format($order->total_copies) }}</td>
        <td>{{ $order->colors }}</td>
        <td>{{ str($order->printing_style)->headline() }}</td>
    </tr>
</table>
@php($calc = $order->calculations->sortByDesc('computed_at')->first())
@if($calc)
<table>
    <tr><th>Pages/Sheet</th><th>Raw Sheets</th><th>Wastage %</th><th>Total Sheets</th><th>Reams</th><th>Quires</th></tr>
    <tr>
        <td>{{ $calc->pages_per_sheet }}</td>
        <td>{{ $calc->raw_sheets }}</td>
        <td>{{ $calc->wastage_percentage }}</td>
        <td>{{ $calc->total_sheets }}</td>
        <td>{{ $calc->reams }}</td>
        <td>{{ $calc->quires }}</td>
    </tr>
</table>
@endif
</body>
</html>
