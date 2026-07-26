<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Override;

class StoreReviewRequest extends FormRequest
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
        /** @var Course $course */
        $course = $this->route('course');

        return [
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Course $course */
            $course = $this->route('course');
            $user = $this->user();

            if (! $user->isEnrolledIn($course)) {
                $validator->errors()->add('course', 'You must be enrolled in this course before submitting a review.');
                return;
            }

            $alreadyReviewed = Review::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->exists();

            if ($alreadyReviewed) {
                $validator->errors()->add('course', 'You have already reviewed this course.');
            }
        });
    }

    #[Override]
    public function messages(): array
    {
        return [
            'rating.required' => 'The rating field is required.',
            'rating.min'      => 'The rating must be at least 1.',
            'rating.max'      => 'The rating must not be greater than 5.',
        ];
    }
}