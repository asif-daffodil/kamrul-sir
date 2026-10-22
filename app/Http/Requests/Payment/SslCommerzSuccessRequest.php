<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class SslCommerzSuccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tran_id' => ['required', 'string', 'max:100'],
            'val_id' => ['nullable', 'string', 'max:100'],
            'amount' => ['nullable'],
            'card_type' => ['nullable', 'string'],
            'currency' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ];
    }
}
