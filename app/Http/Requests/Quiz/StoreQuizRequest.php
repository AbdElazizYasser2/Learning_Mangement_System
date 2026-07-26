<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreQuizRequest extends FormRequest
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
            'title'             => ['required', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'time_limit'        => ['nullable', 'integer', 'min:1'],
            'total_marks'       => ['required', 'integer', 'min:1'],
            'passing_score'     => ['sometimes', 'integer', 'min:0', 'max:100'],
            'attempts_allowed'  => ['sometimes', 'integer', 'min:1', 'max:255'],
            'is_published'      => ['sometimes', 'boolean'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'title.required'       => 'The quiz title is required.',

            'total_marks.required' => 'The total marks field is required.',
            'total_marks.min'      => 'The total marks must be greater than 0.',

            'passing_score.max'    => 'The passing score must not be greater than 100.',

            'attempts_allowed.min' => 'At least one attempt must be allowed.',
        ];
    }
}