<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'title',
        'content',
        'video_url',
        'duration',
        'is_preview',
        'order',
    ];

    protected $casts = [
        'duration'   => 'integer',
        'is_preview' => 'boolean',
        'order'      => 'integer',
    ];

    /*
     * Relationships
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function course(): HasOneThrough
    {
        return $this->hasOneThrough(Course::class, Section::class, 'id', 'id', 'section_id', 'course_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    /*
     * Helper Methods
     */
    public function isCompletedBy(User $user): bool
    {
        return $this->progress()
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->exists();
    }

    /*
     * Scopes
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }
}