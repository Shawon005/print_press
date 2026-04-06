<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $config['title'] }} | Printing Press Management System</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans">
        <div class="min-h-screen bg-[var(--app-bg)] px-4 py-8 md:px-8">
            <div class="mx-auto max-w-5xl">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[var(--brand)]">Module Form</p>
                        <h1 class="text-3xl font-black tracking-tight text-slate-900">{{ $config['title'] }}</h1>
                    </div>
                    <a href="{{ $module === 'dashboard' ? route('portal.home') : route('portal.page', $module) }}" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm">Back to {{ str($module)->headline() }}</a>
                </div>

                <div class="surface-card p-6 md:p-8">
                        <form method="POST" action="{{ $formAction }}" class="grid gap-5 md:grid-cols-2">
                            @csrf
                            @if ($formMethod !== 'POST')
                                @method($formMethod)
                            @endif
                            @foreach ($config['fields'] as $field)
                            <div class="{{ ($field['type'] ?? 'text') === 'textarea' ? 'md:col-span-2' : '' }}">
                                <label for="{{ $field['name'] }}" class="mb-2 block text-sm font-semibold text-slate-700">{{ $field['label'] }}</label>
                                @if (($field['type'] ?? 'text') === 'select')
                                    @php
                                        $selectOptions = $field['options'] ?? ($options[$field['source']] ?? []);
                                    @endphp
                                    <select id="{{ $field['name'] }}" name="{{ $field['name'] }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
                                        @if (!empty($field['nullable']))
                                            <option value="">Select</option>
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

                        <div class="md:col-span-2 flex flex-wrap gap-3 pt-2">
                            <button type="submit" class="rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20">{{ $record ? 'Update Record' : 'Save Record' }}</button>
                            <a href="{{ $module === 'dashboard' ? route('portal.home') : route('portal.page', $module) }}" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
