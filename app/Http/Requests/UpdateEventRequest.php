<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = $this->route('event');

        return $event && $this->user() && $event->isManagedBy($this->user());
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'format' => ['required', 'in:online,offline,hybrid'],
            'location' => ['nullable', 'string', 'max:255'],
            'online_url' => ['nullable', 'url', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'registration_start_at' => ['required', 'date', 'before:start_at'],
            'registration_end_at' => ['required', 'date', 'after:registration_start_at', 'before_or_equal:start_at'],
        ];
    }
}
