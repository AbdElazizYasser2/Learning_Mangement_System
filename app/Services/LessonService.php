<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LessonService
{
    public function getBySection(Section $section): Collection
    {
        return $section->lessons()->ordered()->get();
    }

    public function find(Section $section, string $id): Lesson
    {
        return $section->lessons()->findOrFail($id);
    }

    public function create(Section $section, array $data): Lesson
    {
        $data['section_id'] = $section->id;
        $data['order'] = $data['order'] ?? $this->nextOrder($section);

        return Lesson::create($data);
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        $lesson->update($data);
        return $lesson;
    }

    public function reorder(Section $section, array $orderedIds): void
    {
        DB::transaction(function () use ($section, $orderedIds) {
            foreach ($orderedIds as $index => $lessonId) {
                $section->lessons()
                    ->whereKey($lessonId)
                    ->update(['order' => $index + 1]);
            }
        });
    }

    public function delete(Lesson $lesson): bool
    {
        return $lesson->delete();
    }

    protected function nextOrder(Section $section): int
    {
        return ($section->lessons()->max('order') ?? 0) + 1;
    }
}
