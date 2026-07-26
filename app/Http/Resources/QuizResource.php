<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'section_id'        => $this->section_id,
            'title'             => $this->title,
            'description'       => $this->description,
            'time_limit'        => $this->time_limit,
            'total_marks'       => $this->total_marks,
            'passing_score'     => $this->passing_score,
            'attempts_allowed'  => $this->attempts_allowed,
            'is_published'      => $this->is_published,
            'questions_count'   => $this->whenCounted('questions'),
            'section'           => new SectionResource($this->whenLoaded('section')),
            'created_at'        => $this->created_at?->toISOString(),
            'updated_at'        => $this->updated_at?->toISOString(),  
        ];
    }
}