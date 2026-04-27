<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'job_order_id' => ['nullable', 'exists:job_orders,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'po_number' => ['required', 'string', 'max:50'],
            'order_date' => ['nullable', 'date'],
            'expected_date' => ['nullable', 'date'],
            'status' => ['required', 'string', 'max:50'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
