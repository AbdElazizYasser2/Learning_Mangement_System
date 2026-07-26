<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SectionService
{
    public function getByCourse(Course $course): Collection
    {
        return $course->sections()->ordered()->get();
    }

    public function find(Course $course, string $id): Section
    {
        return $course->sections()->findOrFail($id);
    }

    public function create(Course $course, array $data): Section
    {
        $data['course_id'] = $course->id;
        $data['order'] = $data['order'] ?? $this->nextOrder($course);

        return Section::create($data);
    }

    public function update(Section $section, array $data): Section
    {
        $section->update($data);
        return $section;
    }

    public function reorder(Course $course, array $orderedIds): void
    {
        DB::transaction(function () use ($course, $orderedIds) {
            foreach ($orderedIds as $index => $sectionId) {
                $course->sections()
                    ->whereKey($sectionId)
                    ->update(['order' => $index + 1]);
            }
        });
    }

    public function delete(Section $section): bool
    {
        return $section->delete();
    }

    protected function nextOrder(Course $course): int
    {
        return ($course->sections()->max('order') ?? 0) + 1;
    }
}
