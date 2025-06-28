<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNurseRequest extends FormRequest
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
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],
            'role'          => ['required', 'string', 'in:nurse,patient'], // Ensure role is set to 'nurse'
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required'  => ':attribute wajib diisi.',
            'email'     => 'Format :attribute tidak valid.',
            'unique'    => ':attribute ini sudah terdaftar.',
            'min'       => ':attribute minimal :min karakter.',
            'max'       => ':attribute maksimal :max karakter.',
            'string'    => ':attribute harus berupa teks.',
        ];
    }

    /**
     * Get the custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'      => 'Nama lengkap',
            'email'     => 'Alamat email',
            'password'  => 'Kata sandi',
        ];
    }
}
