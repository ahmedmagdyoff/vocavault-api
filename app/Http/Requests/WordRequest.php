<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WordRequest extends FormRequest
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
            'word' => ['required', 'string', 'max:255'],
            'meaning' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'video_id' => ['required', 'integer', 'exists:videos,id'],
            'forms' => ['nullable', 'array'],
            'forms.*.form_type' => ['required', 'string'],
            'forms.*.value' => ['required', 'string'],
        ];
    }
}
