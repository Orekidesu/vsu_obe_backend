<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramProposalRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_proposal_id',
        'curriculum_course_id',
        'level',
        'section',
        'details',
    ];

    protected $casts = [
        'level' => ['string'],
    ];

    public function programProposal(): BelongsTo
    {
        return $this->belongsTo(ProgramProposal::class, 'program_proposal_id');
    }

    public function curriculumCourse(): BelongsTo
    {
        return $this->belongsTo(CurriculumCourse::class, 'curriculum_course_id');
    }
}