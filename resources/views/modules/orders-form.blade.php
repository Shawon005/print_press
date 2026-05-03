<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $record ? 'Edit Printing Order' : 'Create Printing Order' }} | Printing Press Management System</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans">
        <div class="min-h-screen bg-[var(--app-bg)] px-4 py-8 md:px-8" x-data="orderWizard()">
            <div class="mx-auto max-w-6xl">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">Orders Module</p>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ $record ? 'Edit Printing Order' : 'Create Printing Order' }}</h1>
                    </div>
                    <a href="{{ route('portal.page', 'orders') }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Back to Orders</a>
                </div>

                <div class="surface-card p-6 md:p-8 ">
                    <div class="mb-4 flex flex-wrap gap-2 text-xs font-semibold">
                        <template x-for="(s, idx) in steps" :key="idx">
                            <button type="button" class="rounded-full px-3 py-1" :class="step === (idx + 1) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600'" @click="step = idx + 1" x-text="(idx + 1) + '. ' + s"></button>
                        </template>
                    </div>

                    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="space-y-6" @input.debounce.500ms="preview()">
                        @csrf
                        @if ($formMethod !== 'POST') @method($formMethod) @endif

                        <div x-show="step===1" class="grid gap-5 md:grid-cols-3">
                            <div><label class="mb-2 block text-sm font-semibold">Job Number</label><input name="job_number" value="{{ old('job_number', $record?->job_number) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required></div>
                            <div><label class="mb-2 block text-sm font-semibold">Job Title</label><input name="job_title" value="{{ old('job_title', $record?->job_title) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required></div>
                            <div><label class="mb-2 block text-sm font-semibold">Customer</label><select name="customer_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required><option value="">Select</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id', $record?->customer_id) == $customer->id)>{{ $customer->company_name }}</option>@endforeach</select></div>
                            <div><label class="mb-2 block text-sm font-semibold">Order Date</label><input type="date" name="order_date" value="{{ old('order_date', optional($record?->order_date)->toDateString() ?? now()->toDateString()) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                            <div><label class="mb-2 block text-sm font-semibold">Due Date</label><input type="date" name="due_date" value="{{ old('due_date', optional($record?->due_date)->toDateString()) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                            <div><label class="mb-2 block text-sm font-semibold">Status</label><select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><option value="draft" @selected(old('status', $record?->status) === 'draft')>Draft</option><option value="confirmed" @selected(old('status', $record?->status) === 'confirmed')>Confirmed</option><option value="quality_check" @selected(old('status', $record?->status) === 'quality_check')>Quality Check</option><option value="delivered" @selected(old('status', $record?->status) === 'delivered')>Delivered</option></select></div>
                        </div>
                        <div x-show="step===2" class="grid gap-5 md:grid-cols-3">
                            <div><label class="mb-2 block text-sm font-semibold">Paper Type</label><select x-model="form.paper_type_id" name="paper_type_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required><option value="">Select</option>@foreach($paperTypes as $paperType)<option value="{{ $paperType->id }}" @selected(old('paper_type_id', $record?->paper_type_id) == $paperType->id)>{{ $paperType->name }}</option>@endforeach</select></div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold">Raw Material</label>
                                <select x-model="form.raw_material_id" name="raw_material_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required>
                                    <option value="">Select</option>
                                    @foreach($rawMaterials as $rawMaterial)
                                        <option value="{{ $rawMaterial->id }}" @selected(old('raw_material_id', $record?->raw_material_id) == $rawMaterial->id)>{{ $rawMaterial->name }} ({{ $rawMaterial->width_in }}x{{ $rawMaterial->height_in }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label class="mb-2 block text-sm font-semibold">GSM</label><input type="number" x-model="form.gsm" name="gsm" value="{{ old('gsm', $record?->gsm) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required></div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold">Ink Type</label>
                                <select name="ink_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required>
                                    @forelse($inkTypes as $inkType)
                                        <option value="{{ $inkType->name }}" @selected(old('ink_type', $record?->ink_type ?? 'CMYK') === $inkType->name)>{{ $inkType->name }}{{ $inkType->pantone_code ? ' (' . $inkType->pantone_code . ')' : '' }}</option>
                                    @empty
                                        <option value="CMYK" @selected(old('ink_type', $record?->ink_type ?? 'CMYK') === 'CMYK')>CMYK</option>
                                        <option value="Pantone" @selected(old('ink_type', $record?->ink_type ?? 'CMYK') === 'Pantone')>Pantone</option>
                                    @endforelse
                                </select>
                            </div>
                            <div><label class="mb-2 block text-sm font-semibold">Pantone Codes</label><input name="pantone_codes" value="{{ old('pantone_codes', $record?->pantone_codes) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                            <div><label class="mb-2 block text-sm font-semibold">Finish</label><select name="finish_type" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><option value="none" @selected(old('finish_type', $record?->finish_type) === 'none')>None</option><option value="lamination" @selected(old('finish_type', $record?->finish_type) === 'lamination')>Lamination</option><option value="die-cut" @selected(old('finish_type', $record?->finish_type) === 'die-cut')>Die-cut</option><option value="spot-uv" @selected(old('finish_type', $record?->finish_type) === 'spot-uv')>Spot UV</option><option value="varnish" @selected(old('finish_type', $record?->finish_type) === 'varnish')>Varnish</option></select></div>
                            <div><label class="mb-2 block text-sm font-semibold">Page Size</label><select x-model="form.page_size" name="page_size" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"><option value="A4">A4</option><option value="A5">A5</option><option value="Letter">Letter</option><option value="8.5x11">8.5x11</option><option value="custom">Custom</option></select></div>
                            <div><label class="mb-2 block text-sm font-semibold">Custom Width (in)</label><input type="number" step="0.01" x-model="form.custom_width" name="custom_width" value="{{ old('custom_width', $record?->custom_width) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                            <div><label class="mb-2 block text-sm font-semibold">Custom Height (in)</label><input type="number" step="0.01" x-model="form.custom_height" name="custom_height" value="{{ old('custom_height', $record?->custom_height) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                            <div><label class="mb-2 block text-sm font-semibold">Total Copies</label><input type="number" x-model="form.total_copies" name="total_copies" value="{{ old('total_copies', $record?->total_copies) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required></div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold">Standard Sheet</label>
                                <select x-model="form.standard_sheet_size" name="standard_sheet_size" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                                    @forelse($standardSheets as $sheet)
                                        <option value="{{ $sheet->code }}" @selected(old('standard_sheet_size', $record?->standard_sheet_size ?? 'demy') === $sheet->code)>{{ $sheet->name }} ({{ $sheet->width_in }}x{{ $sheet->height_in }})</option>
                                    @empty
                                        <option value="demy">Demy</option><option value="crown">Crown</option><option value="double_crown">Double Crown</option><option value="royal">Royal</option>
                                    @endforelse
                                </select>
                            </div>
                            <div><label class="mb-2 block text-sm font-semibold">Colors</label><input type="number" min="1" max="4" x-model="form.colors" name="colors" value="{{ old('colors', $record?->colors ?? 4) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" required></div>
                        </div>

                        <div x-show="step===3" class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                            <h3 class="text-lg font-bold">Calculation Preview</h3>
                            <div class="mt-4 grid gap-3 md:grid-cols-4 text-sm">
                                <div><p class="text-slate-500">Raw Size</p><p class="font-bold" x-text="calc.sheet_width && calc.sheet_height ? (calc.sheet_width + ' x ' + calc.sheet_height) : '-'"></p></div>
                                <div><p class="text-slate-500">Pages/Sheet</p><p class="font-bold" x-text="calc.pages_per_sheet ?? '-' "></p></div>
                                <div><p class="text-slate-500">Raw Sheets Need</p><p class="font-bold" x-text="calc.raw_sheets ?? '-' "></p></div>
                                <div><p class="text-slate-500">Wastage</p><p class="font-bold" x-text="calc.wastage_percentage ?? '-' "></p></div>
                                <div><p class="text-slate-500">Total Sheets</p><p class="font-bold" x-text="calc.total_sheets ?? '-' "></p></div>
                                <div><p class="text-slate-500">Raw Unit Cost</p><p class="font-bold" x-text="calc.raw_material_unit_cost ?? '-' "></p></div>
                                <div><p class="text-slate-500">Total Raw Cost</p><p class="font-bold" x-text="calc.total_raw_material_cost ?? '-' "></p></div>
                            </div>
                            <p class="mt-3 text-sm font-semibold" x-text="calc.summary || 'Preview will appear automatically.'"></p>
                        </div>

                        <div x-show="step===4" class="grid gap-5 md:grid-cols-3">
                            <div><label class="mb-2 block text-sm font-semibold">Estimated Material Cost</label><input type="number" step="0.01" name="estimated_material_cost" value="{{ old('estimated_material_cost', $record?->estimated_material_cost ?? 0) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                            <div><label class="mb-2 block text-sm font-semibold">Estimated Other Cost</label><input type="number" step="0.01" name="estimated_other_cost" value="{{ old('estimated_other_cost', $record?->estimated_other_cost ?? 0) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                            <div><label class="mb-2 block text-sm font-semibold">Estimated Unit Price</label><input type="number" step="0.01" name="estimated_unit_price" value="{{ old('estimated_unit_price', $record?->estimated_unit_price ?? 0) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"></div>
                            <div>
                                <label class="mb-2 block text-sm font-semibold">Design Source</label>
                                <select name="design_source" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">
                                    <option value="customer_provided" @selected(old('design_source', $record?->design_source ?? 'customer_provided') === 'customer_provided')>Customer Provided</option>
                                    <option value="in_house" @selected(old('design_source', $record?->design_source ?? 'customer_provided') === 'in_house')>In House</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-semibold">Design File (Image/PDF)</label>
                                <input type="file" name="design_file" accept=".jpg,.jpeg,.png,.webp,.pdf" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm">
                                @if ($record?->design_file_path)
                                    <p class="mt-2 text-xs text-slate-500">Current file: <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($record->design_file_path) }}" target="_blank" class="font-semibold text-[var(--brand)] hover:underline">{{ $record->design_file_name ?: 'View uploaded design' }}</a></p>
                                @endif
                            </div>
                            <div class="md:col-span-3"><label class="mb-2 block text-sm font-semibold">Notes</label><textarea name="notes" rows="3" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm">{{ old('notes', $record?->notes) }}</textarea></div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                            <button type="button" @click="step = Math.max(1, step - 1)" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700">Previous</button>
                            <div class="flex gap-2">
                                <button type="button" @click="step = Math.min(4, step + 1)" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700">Next</button>
                                <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">{{ $record ? 'Update Order' : 'Save Order' }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function orderWizard() {
                return {
                    step: 1,
                    steps: ['Customer & Job', 'Paper & Print', 'Live Calculation', 'Financial Terms'],
                    form: {
                        page_size: '{{ old('page_size', $record?->page_size ?? 'A4') }}',
                        custom_width: '{{ old('custom_width', $record?->custom_width ?? '') }}',
                        custom_height: '{{ old('custom_height', $record?->custom_height ?? '') }}',
                        total_copies: {{ (int) old('total_copies', $record?->total_copies ?? 0) }},
                        raw_material_id: '{{ old('raw_material_id', $record?->raw_material_id ?? '') }}',
                        standard_sheet_size: '{{ old('standard_sheet_size', $record?->standard_sheet_size ?? 'demy') }}',
                        colors: {{ (int) old('colors', $record?->colors ?? 4) }},
                        gsm: {{ (int) old('gsm', $record?->gsm ?? 0) }},
                        paper_type_id: '{{ old('paper_type_id', $record?->paper_type_id ?? '') }}'
                    },
                    calc: {},
                    async preview() {
                        if (!this.form.total_copies || !this.form.raw_material_id) return;
                        const response = await fetch("{{ route('orders.calc-preview') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify(this.form),
                        });
                        if (response.ok) this.calc = await response.json();
                    }
                }
            }
</script>
@if (session('locale', 'en') === 'bn')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = {
            'Orders Module':'অর্ডার মডিউল','Back to Orders':'অর্ডারে ফিরুন','Job Number':'জব নম্বর','Job Title':'জব শিরোনাম',
            'Customer':'কাস্টমার','Order Date':'অর্ডারের তারিখ','Due Date':'ডেলিভারির তারিখ','Status':'স্ট্যাটাস',
            'Paper Type':'পেপার টাইপ','Raw Material':'র ম্যাটেরিয়াল','GSM':'জিএসএম','Ink Type':'ইঙ্ক টাইপ',
            'Pantone Codes':'প্যান্টোন কোড','Finish':'ফিনিশ','Page Size':'পেইজ সাইজ','Custom Width (in)':'কাস্টম প্রস্থ (ইঞ্চি)',
            'Custom Height (in)':'কাস্টম উচ্চতা (ইঞ্চি)','Total Copies':'মোট কপি','Standard Sheet':'স্ট্যান্ডার্ড শিট',
            'Colors':'রং','Calculation Preview':'ক্যালকুলেশন প্রিভিউ','Estimated Material Cost':'আনুমানিক ম্যাটেরিয়াল খরচ',
            'Estimated Other Cost':'আনুমানিক অন্যান্য খরচ','Estimated Unit Price':'আনুমানিক ইউনিট মূল্য','Design Source':'ডিজাইন সোর্স',
            'Design File (Image/PDF)':'ডিজাইন ফাইল (ইমেজ/পিডিএফ)','Notes':'নোট','Previous':'আগের','Next':'পরের',
            'Update Order':'অর্ডার আপডেট','Save Order':'অর্ডার সেভ'
        };
        document.querySelectorAll('label,button,a,h1,h3,p,span,option').forEach(function (el) {
            const t = el.textContent.trim();
            if (map[t]) el.textContent = t + '/' + map[t];
        });
    });
</script>
@endif
    </body>
</html>
