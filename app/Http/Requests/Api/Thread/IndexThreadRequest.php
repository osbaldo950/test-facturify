<?php

namespace App\Http\Requests\Api\Thread;

use App\Enums\ThreadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexThreadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', Rule::enum(ThreadStatus::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.Illuminate\Validation\Rules\Enum' => 'El estado debe ser open, closed o pending.',
            'per_page.integer' => 'El número de elementos por página debe ser un entero.',
            'per_page.min' => 'Debe solicitar al menos 1 elemento por página.',
            'per_page.max' => 'No puede solicitar más de 100 elementos por página.',
        ];
    }
}
