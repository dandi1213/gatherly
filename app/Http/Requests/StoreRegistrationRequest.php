<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Hanya user terautentikasi yang boleh mendaftar (D2).
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'ticket_type_id' => [
                'required',
                'integer',
                Rule::exists('ticket_types', 'id')->where('event_id', $event->id),
            ],
            'attendee_name' => ['required', 'string', 'max:255'],
            'attendee_email' => ['required', 'email', 'max:255'],
            'attendee_phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ticket_type_id.exists' => 'Tipe tiket tidak valid untuk event ini.',
            'attendee_name.required' => 'Nama peserta wajib diisi.',
            'attendee_email.required' => 'Email peserta wajib diisi.',
            'attendee_email.email' => 'Format email peserta tidak valid.',
        ];
    }
}
