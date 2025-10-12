<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.attributes.name' => 'required|string|max:255',
            'data.attributes.email' => 'required|email|max:255',
            'data.attributes.company' => 'required|string',
            'data.attributes.phone' => 'required|string|max:20',
            'data.attributes.message' => 'required|string',
        ];
    }
}
