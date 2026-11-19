<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateTicketTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event && $this->user() && $event->isManagedBy($this->user());
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quota' => ['required', 'integer', 'min:1'],
            'available_from' => ['nullable', 'date'],
            'available_until' => ['nullable', 'date', 'after:available_from'],
            'is_active' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $ticketType = $this->route('ticket_type');

                // Kuota tidak boleh diturunkan di bawah jumlah tiket yang sudah terbit (PRD 7.2)
                if ($ticketType && $this->filled('quota')) {
                    $issued = $ticketType->issued_count;

                    if ((int) $this->input('quota') < $issued) {
                        $validator->errors()->add(
                            'quota',
                            "Kuota tidak boleh lebih kecil dari jumlah tiket yang sudah terbit ({$issued})."
                        );
                    }
                }
            },
        ];
    }
}
