<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'slug' => [
                'required',
                'alpha_dash',
                'max:120',
                Rule::unique('posts', 'slug')->ignore($this->get('id') ?? $this->route('id')),
            ],
            'title' => ['required', 'max:255'],
            'read_minutes' => ['required', 'integer', 'min:1', 'max:67'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
