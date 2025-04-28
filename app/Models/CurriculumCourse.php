<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurriculumCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_id',
        'course_id',
        'course_category_id',
        'semester_id',
        'unit',
    ];

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function pos(): BelongsToMany
    {
        return $this->belongsToMany(ProgramOutcome::class, 'curriculum_course_po', 'curriculum_course_id', 'po_id')
            ->withPivot('ird');
    }

    public function committees(): BelongsToMany
    {
        return $this->belongsToMany(
            Committee::class,
            'committee_course_assignments',
            'curriculum_course_id',
            'committee_id'
        )->withTimestamps();
    }

    public function cos(): HasMany
    {
        return $this->hasMany(CourseOutcome::class, 'curriculum_course_id');
    }
}