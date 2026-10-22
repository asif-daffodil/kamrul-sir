<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class SslCommerzFailureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tran_id' => ['required', 'string', 'max:100'],
            'error' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ];
    }
}
