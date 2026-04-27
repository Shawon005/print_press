<?php

namespace App\Http\Requests;

use App\Models\JobPayment;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', JobPayment::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'job_order_id' => ['required', 'exists:job_orders,id'],
            'payment_stage' => ['required', 'in:advance,partial,final'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank,bKash'],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
