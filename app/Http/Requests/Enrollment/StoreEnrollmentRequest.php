<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class StoreEnrollmentRequest extends FormRequest
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
        return [
            'course_id' => ['required', 'integer',
                Rule::exists('courses', 'id')
                    ->where('is_published', true)
                    ->whereNull('deleted_at'),
                Rule::unique('enrollments', 'course_id')
                    ->where('user_id', $this->user()->id),
            ],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'course_id.required' => 'The course is required.',
            'course_id.exists'   => 'The selected course is invalid or is not currently published.',
            'course_id.unique'   => 'You are already enrolled in this course.',
        ];
    }
}
