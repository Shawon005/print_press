<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Document</title></head>
<body>
<h2>{{ $jobOrder->job_number ?? $deliveryChallan->challan_number ?? 'Document' }}</h2>
<p>Generated from Printing Press ERP.</p>
</body>
</html>
