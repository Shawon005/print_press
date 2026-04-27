<?php

namespace App\Http\Requests;

use App\Models\DeliveryChallan;
use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryChallanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', DeliveryChallan::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'job_order_id' => ['required', 'exists:job_orders,id'],
            'challan_number' => ['required', 'string', 'max:50'],
            'delivery_date' => ['required', 'date'],
            'receiver_name' => ['required', 'string', 'max:255'],
            'signature_note' => ['nullable', 'string'],
        ];
    }
}
