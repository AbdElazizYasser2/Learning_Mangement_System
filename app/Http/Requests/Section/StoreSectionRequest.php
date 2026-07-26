<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreSectionRequest extends FormRequest
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
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order'       => ['sometimes', 'integer', 'min:1'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'title.required' => 'The section title is required.',
            'order.min'      => 'The section order must be at least 1.',
        ];
    }
}
