<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
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
            'name'              => ['required', 'string', 'max:255'],
            'description'       => ['required', 'string', 'max:1000'],
            'max_capacity'      => ['required', 'integer', 'min:1', 'max:100'],
            'price_per_day'     => ['required', 'numeric', 'min:1'],
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
            'required'      => ':attribute wajib diisi.',
            'string'        => ':attribute harus berupa teks.',
            'integer'       => ':attribute harus berupa angka bulat.',
            'numeric'       => ':attribute harus berupa angka.',
            'min'           => ':attribute minimal :min.',
            'max'           => ':attribute maksimal :max.',
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
            'name'              => 'Nama Ruangan',
            'description'       => 'Deskripsi Ruangan',
            'max_capacity'      => 'Kapasitas Maksimal',
            'price_per_day'     => 'Harga per Hari',
        ];
    }
}
