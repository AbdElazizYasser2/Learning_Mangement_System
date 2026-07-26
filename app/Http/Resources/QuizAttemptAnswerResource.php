<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'question_id'        => $this->question_id,
            'question_option_id' => $this->question_option_id,
            'is_correct'         => $this->is_correct,
            'question' => new QuestionWithAnswerResource($this->whenLoaded('question')),
        ];
    }
}