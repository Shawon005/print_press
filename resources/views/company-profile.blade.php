<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Company Profile | Printing Press Management System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">
<div class="min-h-screen bg-[var(--app-bg)] px-4 py-8 md:px-8">
    <div class="mx-auto max-w-6xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">Settings</p>
                <h1 class="text-3xl font-black tracking-tight text-slate-900">Company Profile</h1>
            </div>
            <a href="{{ route('portal.page', 'settings') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Back to Settings</a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-[20px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-[20px] border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <article class="surface-card p-6">
            <form method="POST" action="{{ route('settings.company-profile.update') }}" class="grid gap-4 md:grid-cols-3">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Company Name</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $companyProfile['company_name'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700" required>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Tagline</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $companyProfile['tagline'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $companyProfile['phone'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $companyProfile['email'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Website</label>
                    <input type="text" name="website" value="{{ old('website', $companyProfile['website'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Logo URL</label>
                    <input type="text" name="logo_url" value="{{ old('logo_url', $companyProfile['logo_url'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">VAT No</label>
                    <input type="text" name="vat_no" value="{{ old('vat_no', $companyProfile['vat_no'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">BIN No</label>
                    <input type="text" name="bin_no" value="{{ old('bin_no', $companyProfile['bin_no'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Trade License</label>
                    <input type="text" name="trade_license_no" value="{{ old('trade_license_no', $companyProfile['trade_license_no'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div class="md:col-span-3">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Address</label>
                    <textarea name="address" rows="2" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">{{ old('address', $companyProfile['address'] ?? '') }}</textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Signature Name</label>
                    <input type="text" name="signature_name" value="{{ old('signature_name', $companyProfile['signature_name'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Signature Title</label>
                    <input type="text" name="signature_title" value="{{ old('signature_title', $companyProfile['signature_title'] ?? '') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                </div>
                <div class="md:col-span-3">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Quotation Footer Note</label>
                    <textarea name="quotation_footer_note" rows="2" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">{{ old('quotation_footer_note', $companyProfile['quotation_footer_note'] ?? '') }}</textarea>
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">Save Company Profile</button>
                </div>
            </form>
        </article>
    </div>
</div>
</body>
</html>
