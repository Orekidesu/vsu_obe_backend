<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseOutcome extends Model
{
    use HasFactory;
    protected $fillable = [
        'curriculum_course_id',
        'name',
        'statement'
    ];


    public function curriculumCourse(): BelongsTo
    {
        return $this->belongsTo(CurriculumCourse::class, 'curriculum_course_id');
    }

    public function pos(): BelongsToMany
    {
        return $this->belongsToMany(
            ProgramOutcome::class,
            'course_outcome_po',
            'co_id',
            'po_id'
        )->withTimestamps();
    }

    public function abcd(): HasOne
    {
        return $this->hasOne(CourseOutcomeABCD::class, 'co_id');
    }
}