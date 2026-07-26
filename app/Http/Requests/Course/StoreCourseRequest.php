<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class StoreCourseRequest extends FormRequest
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
            'category_id'   => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'price'         => ['required', 'numeric', 'min:0', 'max:99999.99'],
            'thumbnail'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'preview_video' => ['nullable', 'file', 'mimes:mp4,mov,avi', 'max:51200'], // 50MB
            'duration'      => ['nullable', 'integer', 'min:1'],
            'level'         => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'is_published'  => ['sometimes', 'boolean'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'category_id.required' => 'The course category is required.',
            'category_id.exists'   => 'The selected course category is invalid.',

            'name.required'        => 'The course name is required.',

            'price.required'       => 'The course price is required.',
            'price.min'            => 'The course price must be at least 0.',

            'thumbnail.image'      => 'The thumbnail must be an image.',
            'thumbnail.max'        => 'The thumbnail must not be greater than 2 MB.',

            'preview_video.mimes'  => 'The preview video must be a file of type: mp4, mov, avi.',
            'preview_video.max'    => 'The preview video must not be greater than 50 MB.',

            'level.required'       => 'The course level is required.',
            'level.in'             => 'The selected course level is invalid.',
        ];
    }
}
