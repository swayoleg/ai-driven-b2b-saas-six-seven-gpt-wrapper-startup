<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:60'],
            'network' => ['required', 'max:40'],
            'address' => ['required', 'string', 'min:20', 'max:120'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
