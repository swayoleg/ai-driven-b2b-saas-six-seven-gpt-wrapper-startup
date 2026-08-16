<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
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
                Rule::unique('pages', 'slug')->ignore($this->get('id') ?? $this->route('id')),
            ],
            'template' => ['required', Rule::in(array_keys(\App\Models\Page::TEMPLATES))],
            'title' => ['required', 'max:255'],
            'meta_title' => ['nullable', 'max:255'],
            'meta_description' => ['nullable', 'max:500'],
        ];
    }
}
