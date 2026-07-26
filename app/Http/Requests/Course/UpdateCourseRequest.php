<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class UpdateCourseRequest extends FormRequest
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
            'category_id'   => ['sometimes', 'required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name'          => ['sometimes', 'required', 'string', 'max:255'],
            'description'   => ['sometimes', 'nullable', 'string'],
            'price'         => ['sometimes', 'required', 'numeric', 'min:0', 'max:99999.99'],
            'thumbnail'     => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'preview_video' => ['sometimes', 'nullable', 'file', 'mimes:mp4,mov,avi', 'max:51200'],
            'duration'      => ['sometimes', 'nullable', 'integer', 'min:1'],
            'level'         => ['sometimes', 'required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'is_published'  => ['sometimes', 'boolean'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'category_id.exists'  => 'The selected category is invalid.',

            'price.min'           => 'The price must be at least 0.',

            'thumbnail.image'     => 'The thumbnail must be an image.',
            'thumbnail.max'       => 'The thumbnail must not be greater than 2 MB.',

            'preview_video.mimes' => 'The preview video must be a file of type: mp4, mov, or avi.',
            'preview_video.max'   => 'The preview video must not be greater than 50 MB.',
        ];
    }
}