<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
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
            'gender'        => ['required', 'string', 'in:laki-laki,perempuan'],
            'password'      => ['required', 'string', 'min:8'],
            'nik'           => ['required', 'string', 'digits:16', 'unique:patient,nik'],
            'birth_date'    => ['required', 'date', 'before:today'],
        ];
    }

    /**
     * Dapatkan pesan kesalahan validasi kustom untuk aturan yang ditentukan.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'             => 'Nama lengkap wajib diisi.',
            'email.required'            => 'Alamat email wajib diisi.',
            'email.email'               => 'Format email tidak valid.',
            'email.unique'              => 'Alamat email ini sudah terdaftar.',
            'gender.required'           => 'Jenis kelamin wajib dipilih.',
            'gender.in'                 => 'Jenis kelamin yang dipilih tidak valid.',
            'password.required'         => 'Kata sandi wajib diisi.',
            'password.min'              => 'Kata sandi minimal 8 karakter.',
            'nik.required'              => 'NIK wajib diisi.',
            'nik.unique'                => 'NIK ini sudah terdaftar.',
            'nik.digits'                => 'NIK harus terdiri dari 16 digit angka.',
            'birth_date.required'       => 'Tanggal lahir wajib diisi.',
            'birth_date.date'           => 'Format tanggal lahir tidak valid.',
            'birth_date.before'         => 'Tanggal lahir tidak boleh di masa depan.',
        ];
    }
}
