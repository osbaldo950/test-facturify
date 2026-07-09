<?php

namespace App\Http\Requests\Api\Thread;

use Illuminate\Foundation\Http\FormRequest;

class StoreThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject.required' => 'El asunto es obligatorio.',
            'subject.max' => 'El asunto no puede superar los 255 caracteres.',
            'body.required' => 'El contenido del mensaje es obligatorio.',
        ];
    }
}
