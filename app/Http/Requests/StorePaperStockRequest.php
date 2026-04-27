<?php

namespace App\Http\Requests;

use App\Models\PaperStock;
use Illuminate\Foundation\Http\FormRequest;

class StorePaperStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', PaperStock::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'paper_type_id' => ['required', 'exists:paper_types,id'],
            'gsm' => ['required', 'integer', 'min:40', 'max:1000'],
            'sheet_size' => ['required', 'in:demy,crown,double_crown,royal'],
            'stock_reams' => ['required', 'integer', 'min:0'],
            'stock_quires' => ['required', 'integer', 'min:0'],
            'stock_sheets' => ['required', 'integer', 'min:0'],
            'low_stock_threshold_sheets' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
