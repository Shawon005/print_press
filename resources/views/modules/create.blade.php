<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $config['title'] }} | Printing Press Management System</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        @php
            $locale = session('locale', 'en');
            $biMap = [
                'Module Form' => 'মডিউল ফর্ম',
                'Back to' => 'ফিরুন',
                'Select' => 'নির্বাচন করুন',
                'Update Record' => 'রেকর্ড আপডেট',
                'Save Record' => 'রেকর্ড সেভ',
                'Cancel' => 'বাতিল',
                'Select a Job Order to auto-fill subtotal with remaining amount.' => 'বাকি টাকার ভিত্তিতে সাবটোটাল অটো-ফিল করতে জব অর্ডার নির্বাচন করুন।',
                'Loading order balance...' => 'অর্ডারের ব্যালেন্স লোড হচ্ছে...',
                'Could not load remaining amount for this job order.' => 'এই জব অর্ডারের বাকি টাকা লোড করা যায়নি।',
                'Order Total' => 'অর্ডার মোট',
                'Paid' => 'পরিশোধিত',
                'Remaining' => 'বাকি',
            ];
            $bi = function (string $en) use ($locale, $biMap): string {
                if ($locale !== 'bn') return $en;
                $bn = $biMap[$en] ?? null;
                return $bn ? ($en . '/' . $bn) : $en;
            };
            $moduleBackPage = in_array($module, ['paper-types', 'ink-types', 'standard-sheets', 'units'], true) ? 'settings' : $module;
        @endphp
        <div class="min-h-screen bg-[var(--app-bg)] px-4 py-8 md:px-8">
            <div class="mx-auto max-w-5xl">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">{{ $bi('Module Form') }}</p>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ $config['title'] }}</h1>
                    </div>
                    <a href="{{ $module === 'dashboard' ? route('portal.home') : route('portal.page', $moduleBackPage) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">{{ $bi('Back to') }} {{ str($moduleBackPage)->headline() }}</a>
                </div>

                <div class="surface-card p-6 md:p-8">
                        <form method="POST" action="{{ $formAction }}" class="grid gap-5 md:grid-cols-2">
                            @csrf
                            @if ($formMethod !== 'POST')
                                @method($formMethod)
                            @endif
                            @foreach ($config['fields'] as $field)
                            <div class="{{ ($field['type'] ?? 'text') === 'textarea' ? 'md:col-span-2' : '' }}">
                                <label for="{{ $field['name'] }}" class="mb-2 block text-sm font-semibold text-slate-700">{{ $bi($field['label']) }}</label>
                                @if (($field['type'] ?? 'text') === 'select')
                                    @php
                                        $selectOptions = $field['options'] ?? ($options[$field['source']] ?? []);
                                    @endphp
                                    <select id="{{ $field['name'] }}" name="{{ $field['name'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        @if (!empty($field['nullable']))
                                            <option value="">{{ $bi('Select') }}</option>
                                        @endif
                                        @foreach ($selectOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old($field['name'], $record?->{$field['name']}) == $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        id="{{ $field['name'] }}"
                                        name="{{ $field['name'] }}"
                                        type="{{ $field['type'] ?? 'text' }}"
                                        value="{{ ($field['type'] ?? 'text') === 'password' ? '' : old($field['name'], $record?->{$field['name']} ?? (($field['type'] ?? 'text') === 'date' ? now()->toDateString() : '')) }}"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700"
                                    >
                                @endif
                                @error($field['name'])
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach

                        @if ($module === 'invoices')
                            <div class="md:col-span-2">
                                <p id="invoice-job-order-summary" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                    {{ $bi('Select a Job Order to auto-fill subtotal with remaining amount.') }}
                                </p>
                            </div>
                        @endif

                        <div class="md:col-span-2 flex flex-wrap gap-3 pt-2">
                            <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">{{ $record ? $bi('Update Record') : $bi('Save Record') }}</button>
                            <a href="{{ $module === 'dashboard' ? route('portal.home') : route('portal.page', $moduleBackPage) }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700">{{ $bi('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if ($module === 'invoices')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const jobOrderSelect = document.getElementById('job_order_id');
                    const subtotalInput = document.getElementById('subtotal');
                    const customerInput = document.getElementById('customer_id');
                    const summary = document.getElementById('invoice-job-order-summary');

                    if (!jobOrderSelect || !subtotalInput || !summary) {
                        return;
                    }

                    const currency = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                    jobOrderSelect.addEventListener('change', async function () {
                        const jobOrderId = jobOrderSelect.value;
                        if (!jobOrderId) {
                            summary.textContent = @json($bi('Select a Job Order to auto-fill subtotal with remaining amount.'));
                            return;
                        }

                        summary.textContent = @json($bi('Loading order balance...'));

                        try {
                            const response = await fetch(`{{ url('/invoices/job-orders') }}/${jobOrderId}/summary`);
                            if (!response.ok) {
                                throw new Error('Request failed');
                            }

                            const payload = await response.json();
                            subtotalInput.value = Number(payload.remaining_amount || 0).toFixed(2);

                            if (customerInput && payload.customer_id) {
                                customerInput.value = String(payload.customer_id);
                            }

                            summary.textContent =
                                `${@json($bi('Order Total'))}: ${currency.format(payload.total_amount || 0)} | ${@json($bi('Paid'))}: ${currency.format(payload.paid_amount || 0)} | ${@json($bi('Remaining'))}: ${currency.format(payload.remaining_amount || 0)}`;
                        } catch (error) {
                            summary.textContent = @json($bi('Could not load remaining amount for this job order.'));
                        }
                    });
                });
            </script>
        @endif
    </body>
</html>
