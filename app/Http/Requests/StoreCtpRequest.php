<?php

namespace App\Http\Requests;

use App\Models\Ctp;
use Illuminate\Foundation\Http\FormRequest;

class StoreCtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Ctp::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'job_order_id' => ['required', 'exists:job_orders,id'],
            'plate_type' => ['required', 'string', 'max:100'],
            'plate_size' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cost_per_plate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
