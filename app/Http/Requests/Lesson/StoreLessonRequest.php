<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreLessonRequest extends FormRequest
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
            'title'      => ['required', 'string', 'max:255'],
            'content'    => ['nullable', 'string', 'required_without:video_url'],
            'video_url'  => ['nullable', 'url', 'required_without:content'],
            'duration'   => ['nullable', 'integer', 'min:1'],
            'is_preview' => ['sometimes', 'boolean'],
            'order'      => ['sometimes', 'integer', 'min:1'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'title.required'              => 'The lesson title is required.',
            'content.required_without'    => 'Either lesson content or a video URL is required.',
            'video_url.required_without'  => 'Either a video URL or lesson content is required.',
            'video_url.url'               => 'The video URL must be a valid URL.',
        ];
    }
}