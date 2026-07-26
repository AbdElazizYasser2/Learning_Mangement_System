<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canViewContent = $this->is_preview
            || $request->user()?->id === $this->section->course->instructor_id
            || $request->user()?->isAdmin();

        return [
        'id'         => $this->id,
        'section_id' => $this->section_id,
        'title'      => $this->title,
        'content'    => $this->when($canViewContent, $this->content),
        'video_url'  => $this->when($canViewContent, $this->video_url),
        'duration'   => $this->duration,
        'is_preview' => $this->is_preview,
        'order'      => $this->order,
        'created_at' => $this->created_at?->toISOString(),
        'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
