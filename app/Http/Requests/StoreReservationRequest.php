<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
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
            'date_in'     => ['required', 'date', 'after_or_equal:today'],
            'date_out'    => ['required', 'date', 'after:date_in'],
            'total_fee'   => ['required', 'numeric', 'min:0'],
            'patient_id'  => ['required', 'exists:patient,user_id'],
            'nurse_id'    => ['required', 'exists:nurse,user_id'],
            'room_id'     => ['required', 'exists:room,id'],
            'facilities'  => ['array'],
            'facilities.*' => ['exists:facility,id'],
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
            'date'          => ':attribute harus berupa tanggal yang valid.',
            'after_or_equal' => ':attribute harus setelah atau sama dengan hari ini.',
            'after'         => ':attribute harus setelah :date.',
            'numeric'       => ':attribute harus berupa angka.',
            'min'           => ':attribute minimal :min.',
            'exists'        => ':attribute yang dipilih tidak valid.',
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
            'date_in'     => 'Tanggal Masuk',
            'date_out'    => 'Tanggal Keluar',
            'total_fee'   => 'Total Biaya',
            'patient_id'  => 'Pasien',
            'nurse_id'    => 'Perawat',
            'room_id'     => 'Ruangan',
            'facilities'  => 'Fasilitas',
            'facilities.*' => 'Ada fasilitas',
        ];
    }
}
