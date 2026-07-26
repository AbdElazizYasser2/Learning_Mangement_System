<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreCategoryRequest extends FormRequest
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
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['required', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.max'      => 'The category name must not exceed 255 characters.',
            'slug.required' => 'The slug is required.',
            'slug.unique'   => 'The slug is already taken.',
            'icon.max'      => 'The icon field must not exceed 255 characters.',
        ];
    }
}