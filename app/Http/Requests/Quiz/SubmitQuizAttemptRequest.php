<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class SubmitQuizAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $attempt = $this->route('attempt');
        $quizId = $attempt?->quiz_id;

        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'uuid', Rule::exists('questions', 'id')->where('quiz_id', $quizId)->whereNull('deleted_at')],
            'answers.*.question_option_id'   => ['nullable', 'uuid', Rule::exists('question_options', 'id')],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'answers.required'                    => 'The quiz answers are required.',
            'answers.array'                       => 'The answers field must be an array.',

            'answers.*.question_id.required'      => 'The question ID is required.',
            'answers.*.question_id.exists'        => 'One or more selected questions are invalid or do not belong to this quiz.',

            'answers.*.question_option_id.required' => 'The question option ID is required.',
            'answers.*.question_option_id.exists'   => 'One or more selected answer options are invalid.',
        ];
    }
}