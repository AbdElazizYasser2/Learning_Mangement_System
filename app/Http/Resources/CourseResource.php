<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'description'   => $this->description,
            'price'         => (float) $this->price,
            'thumbnail'     => $this->thumbnail ? asset('storage/' . $this->thumbnail) : null,
            'preview_video' => $this->preview_video ? asset('storage/' . $this->preview_video) : null,
            'duration'      => $this->duration,
            'level'         => $this->level?->value,
            'is_published'  => $this->is_published,
            'category'      => new CategoryResource($this->whenLoaded('category')),
            'instructor'    => new UserResource($this->whenLoaded('instructor')),
            'created_at'    => $this->created_at?->toISOString(),
            'updated_at'    => $this->updated_at?->toISOString(),
        ];
    }
}
