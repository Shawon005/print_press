<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Expense</title><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}</style></head>
<body>
<h2>Expense Voucher</h2>
<p><strong>Date:</strong> {{ optional($record->expense_date)->format('d M Y') }}</p>
<p><strong>Category:</strong> {{ $record->category }}</p>
<p><strong>Title:</strong> {{ $record->title }}</p>
<p><strong>Reference:</strong> {{ $record->reference_no }}</p>
<p><strong>Amount:</strong> {{ number_format((float)$record->amount,2) }}</p>
<p><strong>Notes:</strong> {{ $record->notes }}</p>
</body></html>
