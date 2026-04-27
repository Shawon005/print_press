<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation</title>
    <style>
        @page { margin: 14mm 12mm 14mm 12mm; }
        body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #111; font-size: 12px; line-height: 1.35; }
        .sheet { position: relative; min-height: 100%; padding: 14px 22px 20px 34px; }
        .left-strip { position: absolute; left: 0; top: 0; bottom: 0; width: 14px; background: #9ecb26; }
        .top-ribbon { position: absolute; right: 0; top: 0; width: 170px; height: 22px; background: linear-gradient(90deg, #5ca53b, #f08b1d); color: #1f3a1b; font-size: 9px; text-align: center; line-height: 22px; font-weight: 700; }
        .header { margin-bottom: 14px; }
        .logo { max-height: 60px; max-width: 88px; float: left; margin-right: 10px; }
        .brand h1 { margin: 0; font-size: 30px; line-height: 1; font-weight: 800; color: #1b7b37; text-transform: uppercase; }
        .brand p { margin: 2px 0 0; font-size: 11px; color: #2f6137; }
        .clear { clear: both; }
        .center-title { text-align: center; font-size: 34px; margin: 14px 0 10px; font-weight: 700; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta td { vertical-align: top; }
        .meta .right { text-align: right; }
        .subject { margin: 8px 0 10px; font-size: 14px; }
        .subject strong { text-decoration: underline; }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.grid th, table.grid td { border: 1px solid #222; padding: 6px; vertical-align: top; }
        table.grid th { text-align: center; font-size: 12px; font-weight: 700; }
        .sl { width: 8%; text-align: center; }
        .desc { width: 50%; }
        .qty { width: 14%; text-align: center; font-weight: 700; }
        .unit { width: 14%; text-align: center; font-weight: 700; }
        .total { width: 14%; text-align: center; font-weight: 700; }
        .item-title { font-weight: 700; font-size: 13px; }
        .desc-line { margin: 0; }
        .notes { margin-top: 12px; font-size: 13px; }
        .summary { margin-top: 8px; width: 100%; border-collapse: collapse; }
        .summary td { padding: 4px 2px; }
        .summary .label { text-align: right; font-weight: 700; }
        .summary .value { text-align: right; width: 140px; font-weight: 700; }
        .signature { margin-top: 24px; font-size: 12px; }
        .footer { margin-top: 20px; font-size: 12px; color: #444; }
    </style>
</head>
<body>
@php
    $grandTotal = (float) $quotations->sum('total');
    $quoteNumbers = $quotations->pluck('quote_number')->filter()->values()->all();
    $subject = 'Quotation for ' . implode(' & ', $quoteNumbers);
@endphp
<div class="sheet">
    <div class="left-strip"></div>
    <div class="top-ribbon">{{ $companyProfile['tagline'] ?? 'All Kinds of Quality Printing & Packaging' }}</div>

    <div class="header">
        @if (!empty($companyProfile['logo_url']))
            <img src="{{ $companyProfile['logo_url'] }}" alt="Company Logo" class="logo">
        @endif
        <div class="brand">
            <h1>{{ $companyProfile['company_name'] ?? 'Company Name' }}</h1>
            @if (!empty($companyProfile['tagline']))
                <p>{{ $companyProfile['tagline'] }}</p>
            @endif
        </div>
        <div class="clear"></div>
    </div>

    <div class="center-title">Quotation</div>

    <table class="meta">
        <tr>
            <td>
                <strong>{{ $customer?->company_name ?? 'Customer' }}</strong><br>
                {{ $customer?->billing_address ?? $customer?->delivery_address ?? ($customer?->city ?? '-') }}
            </td>
            <td class="right"><strong>Date: {{ optional($printDate)->format('d F Y') }}</strong></td>
        </tr>
    </table>

    <div class="subject"><strong>Subject: {{ $subject }}</strong></div>

    <table class="grid">
        <thead>
            <tr>
                <th class="sl">SL</th>
                <th class="desc">Description</th>
                <th class="qty">Quantity</th>
                <th class="unit">Unit Price</th>
                <th class="total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotations as $index => $quotation)
                @php
                    $qty = (float) $quotation->items->sum('quantity');
                    $unitPrice = $qty > 0 ? ((float) $quotation->total / $qty) : (float) $quotation->total;
                @endphp
                <tr>
                    <td class="sl">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="desc">
                        <div class="item-title">{{ $quotation->quote_number }}</div>
                        @foreach ($quotation->items as $item)
                            <p class="desc-line"><strong>{{ $item->item_name }}:</strong> {{ $item->description ?: 'As per approved sample' }}</p>
                        @endforeach
                        @if (!empty($quotation->notes))
                            <p class="desc-line"><strong>Notes:</strong> {{ $quotation->notes }}</p>
                        @endif
                    </td>
                    <td class="qty">{{ number_format($qty, 0) }} pcs</td>
                    <td class="unit">{{ number_format($unitPrice, 2) }}/-</td>
                    <td class="total">{{ number_format((float) $quotation->total, 2) }}/-</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="notes"><strong>{{ $companyProfile['quotation_footer_note'] ?? 'Note: This Quotation Is Excluding Vat & Tax.' }}</strong></p>

    <table class="summary">
        <tr>
            <td class="label">Grand Total</td>
            <td class="value">{{ number_format($grandTotal, 2) }}/-</td>
        </tr>
    </table>

    <div class="signature">
        ___________________________<br>
        <strong>{{ $companyProfile['signature_name'] ?? 'Authorized Signature' }}</strong><br>
        {{ $companyProfile['signature_title'] ?? 'Proprietor' }}
    </div>

    <div class="footer">
        {{ $companyProfile['address'] ?? '-' }}<br>
        Cell: {{ $companyProfile['phone'] ?? '-' }}<br>
        E-mail: {{ $companyProfile['email'] ?? '-' }}
        @if (!empty($companyProfile['website']))
            <br>Web: {{ $companyProfile['website'] }}
        @endif
    </div>
</div>
</body>
</html>
