<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class ReorderSectionsRequest extends FormRequest
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
        $course = $this->route('course');

        return [
            'section_ids'   => ['required', 'array', 'min:1'],
            'section_ids.*' => ['required', 'integer', Rule::exists('sections', 'id')->where('course_id', $course->id)->whereNull('deleted_at')],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
        'section_ids.required' => 'The section order list is required.',
        'section_ids.array'    => 'The section order list must be an array.',
        'section_ids.min'      => 'The section order list must contain at least one section.',

        'section_ids.*.required' => 'Each section ID is required.',
        'section_ids.*.integer'  => 'Each section ID must be an integer.',
        'section_ids.*.exists'   => 'One or more selected sections are invalid or do not belong to this course.',
        ];
    }
}