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

class Quiz extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    protected $fillable = [
        'section_id',
        'title',
        'description',
        'time_limit',
        'total_marks',
        'passing_score',
        'attempts_allowed',
        'is_published',
    ];

    protected $casts = [
        'time_limit'       => 'integer',
        'total_marks'      => 'integer',
        'passing_score'    => 'integer',
        'attempts_allowed' => 'integer',
        'is_published'     => 'boolean',
    ];

    /*
     * relationships
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function course(): HasOneThrough
    {
        return $this->hasOneThrough(Course::class, Section::class, 'id', 'id', 'section_id', 'course_id');
    }

    public function question(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /*
     * Scopes
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}