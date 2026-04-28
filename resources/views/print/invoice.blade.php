<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #2f3135;
            margin: 0;
            font-size: 12px;
        }
        .page {
            width: 100%;
            box-sizing: border-box;
        }
        .top-band {
            background: #cfcfd1;
            height: 78px;
        }
        .head {
            margin-top: 18px;
        }
        .company-name {
            font-size: 30px;
            font-weight: 800;
            margin: 0;
        }
        .company-web {
            font-size: 14px;
            margin-top: 2px;
        }
        .rule {
            height: 5px;
            width: 240px;
            background: #d1d1d1;
            border-radius: 3px;
            margin: 18px 0 14px;
        }
        .title {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 2px;
            margin: 0 0 16px;
        }
        .row { width: 100%; }
        .col-left { width: 62%; vertical-align: top; display: inline-block; }
        .col-right { width: 36%; vertical-align: top; display: inline-block; text-align: right; }
        .to-label { font-size: 20px; font-weight: 700; margin: 0 0 8px; }
        .customer-name { font-size: 28px; font-weight: 800; margin: 0 0 14px; }
        .meta-line { margin: 7px 0; font-size: 15px; }
        .inv-meta { margin-top: 86px; font-size: 22px; }
        .inv-no { font-size: 28px; font-weight: 700; margin-top: 8px; }

        table { width: 100%; border-collapse: collapse; }
        .items { margin-top: 24px; }
        .items th {
            background: #c6c6c8;
            border: 1px solid #bbbbbb;
            padding: 8px;
            font-size: 17px;
            text-transform: uppercase;
        }
        .items td {
            border: 1px solid #c7c7c7;
            padding: 8px;
            height: 30px;
            background: #efefef;
            font-size: 14px;
        }
        .center { text-align: center; }
        .right { text-align: right; }

        .summary-block { width: 56%; margin-left: 44%; margin-top: 0; }
        .summary-block td {
            border: 1px solid #c7c7c7;
            padding: 9px;
            font-size: 16px;
            background: #efefef;
        }
        .summary-block .grand td {
            background: #c6c6c8;
            font-weight: 800;
            font-size: 20px;
        }

        .payment-terms {
            margin-top: 18px;
            width: 100%;
        }
        .payment-col, .terms-col {
            width: 49%;
            vertical-align: top;
            display: inline-block;
        }
        .section-title {
            font-size: 20px;
            font-weight: 800;
            margin: 0 0 8px;
        }
        .section-text {
            margin: 3px 0;
            font-size: 14px;
            line-height: 1.4;
        }
        .thanks {
            text-align: center;
            margin: 20px 0 14px;
            font-size: 22px;
            font-weight: 800;
        }
        .signature {
            margin-top: 34px;
            text-align: right;
            font-size: 20px;
            font-weight: 700;
        }

        .footer {
            margin-top: 28px;
            background: #c6c6c8;
            border-radius: 22px;
            padding: 12px 18px;
        }
        .footer td {
            width: 33.33%;
            font-size: 14px;
            vertical-align: top;
        }
        .footer-label {
            display: block;
            font-weight: 800;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
@php
    $billToName = $invoice->bill_to_name ?: ($invoice->customer?->company_name ?: '-');
    $billToPhone = $invoice->bill_to_phone ?: ($invoice->customer?->phone ?: '-');
    $billToEmail = $invoice->bill_to_email ?: ($invoice->customer?->email ?: '-');
    $billToAddress = $invoice->bill_to_address ?: ($invoice->customer?->billing_address ?: '-');

    $lineItems = [];
    if ($invoice->jobOrder) {
        $lineItems[] = [
            'description' => $invoice->jobOrder->job_title ?: ('Job ' . ($invoice->jobOrder->job_number ?: '')),
            'qty' => 1,
            'price' => (float) $invoice->subtotal,
            'total' => (float) $invoice->subtotal,
        ];
    }

    if (count($lineItems) === 0) {
        $lineItems[] = [
            'description' => 'Printing Service',
            'qty' => 1,
            'price' => (float) $invoice->subtotal,
            'total' => (float) $invoice->subtotal,
        ];
    }

    $footerPhone = $invoice->footer_phone ?: ($companyProfile['phone'] ?? '-');
    $footerEmail = $invoice->footer_email ?: ($companyProfile['email'] ?? '-');
    $footerAddress = $invoice->footer_address ?: ($companyProfile['address'] ?? '-');
@endphp

<div class="page">
    <div class="top-band"></div>

    <div class="head">
        <h1 class="company-name">{{ $companyProfile['company_name'] ?? 'YOUR COMPANY NAME' }}</h1>
        <div class="company-web">{{ $companyProfile['website'] ?? 'companywebsite.com' }}</div>
        <div class="rule"></div>
        <h2 class="title">INVOICE</h2>
    </div>

    <div class="row">
        <div class="col-left">
            <p class="to-label">To</p>
            <p class="customer-name">{{ $billToName }}</p>
            <p class="meta-line">Phone Number: {{ $billToPhone }}</p>
            <p class="meta-line">Email: {{ $billToEmail }}</p>
            <p class="meta-line">Address: {{ $billToAddress }}</p>
        </div>
        <div class="col-right">
            <div class="inv-meta">Date : {{ optional($invoice->invoice_date)->format('d F Y') ?: now()->format('d F Y') }}</div>
            <div class="inv-no">Invoice no : {{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <table class="items">
        <thead>
        <tr>
            <th style="width:7%;">NO</th>
            <th style="width:37%;">DESCRIPTION</th>
            <th style="width:13%;">QTY</th>
            <th style="width:19%;">PRICE</th>
            <th style="width:24%;">TOTAL</th>
        </tr>
        </thead>
        <tbody>
        @for ($i = 0; isset($lineItems[$i]) && $i < 7; $i++)
            @php($item = $lineItems[$i] ?? null)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $item['description'] ?? '' }}</td>
                <td class="center">{{ $item ? number_format((float) $item['qty'], 2) : '' }}</td>
                <td class="right">{{ $item ? number_format((float) $item['price'], 2) : '' }}</td>
                <td class="right">{{ $item ? number_format((float) $item['total'], 2) : '' }}</td>
            </tr>
        @endfor
        </tbody>
    </table>

    <table class="summary-block">
        <tr>
            <td>Sub Total :</td>
            <td class="right">{{ number_format((float) $invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Tax :</td>
            <td class="right">{{ number_format((float) $invoice->tax, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>GRAND TOTAL :</td>
            <td class="right">{{ number_format((float) $invoice->total, 2) }}</td>
        </tr>
    </table>

    <div class="payment-terms">
        <div class="payment-col">
            <p class="section-title">Payment Method :</p>
            <p class="section-text">{{ $invoice->payment_method_title ?: 'Bank Transfer' }}</p>
            <p class="section-text">Bank Name : {{ $invoice->bank_name ?: '-' }}</p>
            <p class="section-text">Account Number : {{ $invoice->bank_account_number ?: '-' }}</p>
        </div>
        <div class="terms-col">
            <p class="section-title">Term and Conditions :</p>
            <p class="section-text">{{ $invoice->terms_and_conditions ?: 'Custom terms and conditions based on your business.' }}</p>
        </div>
    </div>

    <div class="thanks">THANK YOU FOR BUSINESS!</div>

    <div class="signature">{{ $invoice->signature_label ?: ($companyProfile['signature_name'] ?? 'Authorized Signature') }}</div>

    <table class="footer">
        <tr>
            <td>
                <span class="footer-label">Phone</span>
                {{ $footerPhone }}
            </td>
            <td>
                <span class="footer-label">Mail</span>
                {{ $footerEmail }}
            </td>
            <td>
                <span class="footer-label">Address</span>
                {{ $footerAddress }}
            </td>
        </tr>
    </table>
</div>
</body>
</html>
