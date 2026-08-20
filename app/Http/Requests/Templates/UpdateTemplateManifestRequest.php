<?php

namespace App\Http\Requests\Templates;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateManifestRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'settings' => ['sometimes', 'array'],
            'theme' => ['sometimes', 'array'],
            'pricing' => ['sometimes', 'array'],
            'pricing.type' => ['required_with:pricing', 'string', 'in:free,premium'],
            'pricing.price' => ['nullable', 'numeric', 'min:0.01'],
            'pricing.currency' => ['nullable', 'string', 'size:3'],
        ];
    }
}
