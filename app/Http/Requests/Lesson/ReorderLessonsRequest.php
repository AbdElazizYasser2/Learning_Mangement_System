<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class ReorderLessonsRequest extends FormRequest
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
        $section = $this->route('section');

        return [
            'lesson_ids'   => ['required', 'array', 'min:1'],
            'lesson_ids.*' => ['required', 'integer', Rule::exists('lessons', 'id')->where('section_id', $section->id)->whereNull('deleted_at')]
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'lesson_ids.required' => 'The lesson order list is required.',
            'lesson_ids.array'    => 'The lesson order list must be an array.',
            'lesson_ids.min'      => 'The lesson order list must contain at least one lesson.',

            'lesson_ids.*.required' => 'Each lesson ID is required.',
            'lesson_ids.*.integer'  => 'Each lesson ID must be an integer.',
            'lesson_ids.*.exists'   => 'One or more selected lessons are invalid or do not belong to this section.',
        ];
    }
}
