<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreQuestionRequest extends FormRequest
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
            'text'                 => ['required', 'string'],
            'marks'                => ['required', 'integer', 'min:1'],
            'order'                => ['sometimes', 'integer', 'min:1'],
            'options'              => ['required', 'array', 'min:2'],
            'options.*.text'       => ['required', 'string', 'max:255'],
            'options.*.is_correct' => ['sometimes', 'boolean'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'text.required'    => 'The question text is required.',
            'marks.required'   => 'The question marks are required.',
            'options.required' => 'The answer options are required.',
            'options.min'      => 'Each question must have at least two answer options.',
        ];
    }
}
