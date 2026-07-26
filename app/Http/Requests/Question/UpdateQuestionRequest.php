<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isInstructor() || $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'text'                 => ['sometimes', 'required', 'string'],
            'marks'                => ['sometimes', 'required', 'integer', 'min:1'],
            'order'                => ['sometimes', 'integer', 'min:1'],
            'options'              => ['sometimes', 'array', 'min:2'],
            'options.*.text'       => ['required_with:options', 'string', 'max:255'],
            'options.*.is_correct' => ['sometimes', 'boolean'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'options.min' => 'Each question must have at least two answer options.',
        ];
    }
}
