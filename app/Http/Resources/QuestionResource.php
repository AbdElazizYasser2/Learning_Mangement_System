<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'text'    => $this->text,
            'marks'   => $this->marks,
            'order'   => $this->order,
            'options' => $this->whenLoaded('options', function () {
                return $this->options->map(fn ($option) => [
                    'id'    => $option->id,
                    'text'  => $option->text,
                    'order' => $option->order,
                ]);
            }),
        ];
    }
}