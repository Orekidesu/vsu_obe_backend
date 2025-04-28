<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProgramOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_proposal_id',
        'name',
        'statement',
    ];

    // protected $with = [
    //     'program'
    // ];

    public function programProposal(): BelongsTo
    {
        return $this->belongsTo(ProgramProposal::class, 'program_proposal_id');
    }
    public function peos(): BelongsToMany
    {
        return $this->belongsToMany(ProgramEducationalObjective::class, 'program_outcome_peo', 'po_id', 'peo_id');
    }
    public function gas(): BelongsToMany
    {
        return $this->belongsToMany(GraduateAttribute::class, 'program_outcome_ga', 'po_id', 'ga_id');
    }

    public function curriculumCourses(): BelongsToMany
    {
        return $this->belongsToMany(CurriculumCourse::class, 'curriculum_course_po', 'po_id', 'curriculum_course_id')
            ->withPivot('ird');
    }

    public function cos(): BelongsToMany
    {
        return $this->belongsToMany(
            CourseOutcome::class,
            'course_outcome_po',
            'po_id',
            'co_id',
        )->withTimestamps();
    }
}