<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
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
            'first_name' => 'required | string',
            'last_name' => 'required | string',
            'email' => 'required | email:rfc,strict,filter | unique:users',
            'login' => 'required | unique:users',
            //https://laravel.com/docs/12.x/validation#validating-passwords
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
            'phone' => 'required | regex:/^\d{3}\-\d{3}\-\d{4}$/'
        ];
    }
}
