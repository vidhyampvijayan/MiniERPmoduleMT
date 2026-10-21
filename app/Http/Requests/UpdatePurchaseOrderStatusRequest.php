<?php

namespace App\Http\Requests;

use App\Enums\PurchaseOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    PurchaseOrderStatus::APPROVED->value,
                    PurchaseOrderStatus::RECEIVED->value,
                    PurchaseOrderStatus::CANCELLED->value,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status transition is required.',
            'status.in' => 'Invalid status transition value. Allowed: APPROVED, RECEIVED, CANCELLED.',
        ];
    }
}
