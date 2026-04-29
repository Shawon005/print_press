<x-layouts.app :title="'Create Job Order'" :breadcrumb="'Job Orders / Create'">
    <div x-data="jobOrderWizard()" class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="mb-4 flex items-center gap-2 text-sm">
                <template x-for="(label, idx) in steps" :key="label">
                    <button type="button" @click="step = idx + 1" :class="step === idx + 1 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-full px-3 py-1" x-text="(idx + 1) + '. ' + label"></button>
                </template>
            </div>

            <form method="POST" action="{{ route('job-orders.store') }}" @input.debounce.500ms="preview()" class="space-y-6">
                @csrf
                <div x-show="step === 1" class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium">Job Number</label>
                        <input name="job_number" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Job Title</label>
                        <input name="job_title" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Customer</label>
                        <select name="customer_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required>
                            <option value="">Select customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium">Due Date</label>
                        <input type="date" name="due_date" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                    </div>
                </div>

                <div x-show="step === 2" class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-medium">Paper Type</label>
                        <select name="paper_type_id" x-model="form.paper_type_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required>
                            <option value="">Select</option>
                            @foreach($paperTypes as $paperType)
                                <option value="{{ $paperType->id }}">{{ $paperType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><label class="text-sm font-medium">GSM</label><input type="number" name="gsm" x-model="form.gsm" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required></div>
                    <div><label class="text-sm font-medium">Ink Type</label><input name="ink_type" value="CMYK" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required></div>
                    <div><label class="text-sm font-medium">Pantone Codes</label><input name="pantone_codes" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                    <div>
                        <label class="text-sm font-medium">Finish Type</label>
                        <select name="finish_type" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                            <option value="none">None</option><option value="lamination">Lamination</option><option value="die-cut">Die-cut</option><option value="spot-uv">Spot UV</option><option value="varnish">Varnish</option>
                        </select>
                    </div>
                    <div><label class="text-sm font-medium">Total Pages</label><input type="number" name="total_pages" x-model="form.total_pages" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required></div>
                    <div>
                        <label class="text-sm font-medium">Page Size</label>
                        <select name="page_size" x-model="form.page_size" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required>
                            <option value="A4">A4</option><option value="A5">A5</option><option value="Letter">Letter</option><option value="8.5x11">8.5x11</option><option value="custom">Custom</option>
                        </select>
                    </div>
                    <div><label class="text-sm font-medium">Custom Width (in)</label><input type="number" step="0.01" name="custom_width" x-model="form.custom_width" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                    <div><label class="text-sm font-medium">Custom Height (in)</label><input type="number" step="0.01" name="custom_height" x-model="form.custom_height" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2"></div>
                    <div><label class="text-sm font-medium">Total Copies</label><input type="number" name="total_copies" x-model="form.total_copies" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required></div>
                    <div>
                        <label class="text-sm font-medium">Standard Sheet Size</label>
                        <select name="standard_sheet_size" x-model="form.standard_sheet_size" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required>
                            <option value="demy">Demy</option><option value="crown">Crown</option><option value="double_crown">Double Crown</option><option value="royal">Royal</option>
                        </select>
                    </div>
                    <div><label class="text-sm font-medium">Colors (1-4)</label><input type="number" min="1" max="4" name="colors" x-model="form.colors" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" required></div>
                </div>

                <div x-show="step === 3" class="rounded-lg border border-blue-200 bg-blue-50 p-5">
                    <h3 class="mb-3 text-lg font-semibold">Live Calculation Preview</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                        <div><p class="text-slate-500">Pages/Sheet</p><p class="font-bold" x-text="calculation.pages_per_sheet ?? '-' "></p></div>
                        <div><p class="text-slate-500">Raw Sheets</p><p class="font-bold" x-text="calculation.raw_sheets ?? '-' "></p></div>
                        <div><p class="text-slate-500">Wastage %</p><p class="font-bold" x-text="calculation.wastage_percentage ?? '-' "></p></div>
                        <div><p class="text-slate-500">Total Sheets</p><p class="font-bold" x-text="calculation.total_sheets ?? '-' "></p></div>
                    </div>
                    <p class="mt-3 text-sm font-medium" x-text="calculation.summary"></p>
                </div>

                <div x-show="step === 4" class="grid gap-4 md:grid-cols-3">
                    <div><label class="text-sm font-medium">Estimated Material Cost</label><input type="number" step="0.01" name="estimated_material_cost" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" value="0"></div>
                    <div><label class="text-sm font-medium">Estimated Other Cost</label><input type="number" step="0.01" name="estimated_other_cost" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" value="0"></div>
                    <div><label class="text-sm font-medium">Estimated Unit Price</label><input type="number" step="0.01" name="estimated_unit_price" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" value="0"></div>
                    <div class="md:col-span-3">
                        <label class="text-sm font-medium">Notes</label>
                        <textarea name="notes" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2" rows="3"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="button" @click="step = Math.max(1, step - 1)" class="rounded-lg border border-slate-300 px-4 py-2">Previous</button>
                    <div class="flex gap-2">
                        <button type="button" @click="step = Math.min(4, step + 1)" class="rounded-lg bg-slate-200 px-4 py-2">Next</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white">Create Job Order</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function jobOrderWizard() {
            return {
                step: 1,
                steps: ['Customer & Job Info', 'Paper & Print Specs', 'Live Calculation Preview', 'Financial Terms'],
                form: {
                    total_pages: 0,
                    page_size: 'A4',
                    custom_width: '',
                    custom_height: '',
                    total_copies: 0,
                    standard_sheet_size: 'demy',
                    colors: 4,
                    gsm: 0,
                    paper_type_id: ''
                },
                calculation: {},
                async preview() {
                    if (!this.form.total_pages || !this.form.total_copies) return;
                    const res = await fetch("{{ route('job-orders.preview-calculation') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify(this.form)
                    });
                    if (res.ok) this.calculation = await res.json();
                }
            }
        }
    </script>
</x-layouts.app>
