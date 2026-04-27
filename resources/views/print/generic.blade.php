<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ str($module)->headline() }} Print</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #efefef; width: 240px; }
    </style>
</head>
<body>
<h2>{{ str($module)->headline() }} Record</h2>
<table>
    @foreach($record->getAttributes() as $key => $value)
        @continue(in_array($key, ['password', 'remember_token'], true))
        <tr>
            <th>{{ str($key)->headline() }}</th>
            <td>{{ is_scalar($value) || $value === null ? $value : json_encode($value) }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
