<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WaitlistSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'size' => ['nullable', 'string', 'max:255'],
            'urgency' => ['nullable', 'string', 'max:255'],
            'maturity' => ['nullable', 'string', 'max:255'],
            'pain' => ['nullable', 'string', 'max:2000'],
            'budget' => ['nullable', 'string', 'max:255'],
        ];
    }
}
