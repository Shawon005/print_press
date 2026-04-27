<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Supplier</title><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}</style></head>
<body>
<h2>Supplier Profile</h2>
<p><strong>Company:</strong> {{ $record->company_name }}</p>
<p><strong>Code:</strong> {{ $record->supplier_code }}</p>
<p><strong>Contact:</strong> {{ $record->contact_person }}</p>
<p><strong>Phone:</strong> {{ $record->phone }}</p>
<p><strong>Email:</strong> {{ $record->email }}</p>
<p><strong>Address:</strong> {{ $record->address }}</p>
</body></html>
