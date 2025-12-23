<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam payer int Example: 1
 * @bodyParam payee int Example: 2
 * @bodyParam value float Example: 100.50
 */
class TransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|numeric|decimal:0,2',
            'payer' => 'required|exists:users,id',
            'payee' => 'required|exists:users,id',
        ];
    }
}
