<?php

namespace App\Http\Requests;

use App\Http\Enums\UserTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * @bodyParam first_name string required Example: John
 * @bodyParam last_name string required Example: Doe
 * @bodyParam email string required Example: john.doe@example.com
 * @bodyParam document string required CPF or CNPJ. Example: 12345678901
 * @bodyParam user_type string required common|shop. Example: common
 * @bodyParam password string required Example: password123
 * @bodyParam password_confirmation string required Must match password. Example: password123
 */
class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
            'document' => 'required|string|max:14|min:11|unique:users,document',
            'user_type' => ['required', new Enum(UserTypeEnum::class)],
        ];
    }

    public function messages(): array
    {
        $values = collect(UserTypeEnum::cases())->pluck('value')->implode(', ');

        return [
            'user_type.Illuminate\Validation\Rules\Enum' => 'The selected user type is invalid. It must be one of the following: '.$values,
        ];
    }
}
