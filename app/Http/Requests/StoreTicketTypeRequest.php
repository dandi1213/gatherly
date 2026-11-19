<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketTypeRequest extends FormRequest
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
}
