<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
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
            'quiz_id'       => $this->quiz_id,
            'is_submitted'  => $this->isSubmitted(),
            'started_at'    => $this->started_at?->toISOString(),
            'score'         => $this->when($this->isSubmitted(), $this->score),
            'total_marks'   => $this->when($this->isSubmitted(), fn () => $this->quiz->total_marks),
            'percentage'    => $this->when($this->isSubmitted(), fn () => $this->calculatePercentage()),
            'is_passed'     => $this->when($this->isSubmitted(), $this->is_passed),
            'submitted_at'  => $this->when($this->isSubmitted(), fn () => $this->submitted_at?->toISOString()),
            'answers'       => QuizAttemptAnswerResource::collection($this->whenLoaded('answers')),
            'created_at'    => $this->created_at?->toISOString(),
        ];
    }

    protected function calculatePercentage(): int
    {
        if (! $this->quiz || $this->quiz->total_marks === 0) {
            return 0;
        }

        return (int) round(($this->score / $this->quiz->total_marks) * 100);
    }
}