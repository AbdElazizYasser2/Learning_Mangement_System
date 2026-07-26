<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateLessonRequest extends FormRequest
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
            'title'      => ['sometimes', 'required', 'string', 'max:255'],
            'content'    => ['sometimes', 'nullable', 'string'],
            'video_url'  => ['sometimes', 'nullable', 'url'],
            'duration'   => ['sometimes', 'nullable', 'integer', 'min:1'],
            'is_preview' => ['sometimes', 'boolean'],
            'order'      => ['sometimes', 'integer', 'min:1'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'title.required' => 'The lesson title is required.',
            'video_url.url'  => 'The video URL must be a valid URL.',
        ];
    }
}