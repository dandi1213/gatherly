<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi ditangani policy di controller.
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email pengguna wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}
