<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Committee extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_proposal_id',
        'user_id',
        'created_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function programProposal(): BelongsTo
    {
        return $this->belongsTo(ProgramProposal::class, 'program_proposal_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function curriculumCourses(): BelongsToMany
    {
        return $this->belongsToMany(
            CurriculumCourse::class,
            'committee_course_assignments',
            'committee_id',
            'curriculum_course_id'
        )->withPivot(['is_completed', 'is_in_revision'])->withTimestamps();
    }
}