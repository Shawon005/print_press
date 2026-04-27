<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\JobOrder::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'job_number' => ['required', 'string', 'max:50'],
            'job_title' => ['required', 'string', 'max:255'],
            'customer_id' => ['required', 'exists:customers,id'],
            'order_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'gsm' => ['required', 'integer', 'min:40', 'max:1000'],
            'paper_type_id' => ['required', 'exists:paper_types,id'],
            'ink_type' => ['required', 'string', 'max:50'],
            'pantone_codes' => ['nullable', 'string', 'max:255'],
            'finish_type' => ['required', 'string', 'max:50'],
            'total_pages' => ['required', 'integer', 'min:1'],
            'page_size' => ['required', 'string', 'max:50'],
            'custom_width' => ['nullable', 'numeric', 'min:0.1'],
            'custom_height' => ['nullable', 'numeric', 'min:0.1'],
            'total_copies' => ['required', 'integer', 'min:1'],
            'standard_sheet_size' => ['required', 'in:demy,crown,double_crown,royal'],
            'colors' => ['required', 'integer', 'min:1', 'max:4'],
            'printing_style' => ['required', 'in:work_and_turn,work_and_back'],
            'estimated_material_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_other_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
