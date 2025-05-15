<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramProposalVersionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_proposal_version_id',
        'section',
        'curriculum_course_id',
        'previous_data',
        'new_data',
    ];

    protected $casts = [
        'previous_data' => 'json',
        'new_data' => 'json',
    ];

    public function proposalVersion(): BelongsTo
    {
        return $this->belongsTo(ProgramProposalVersion::class, 'program_proposal_version_id');
    }

    public function curriculumCourse(): BelongsTo
    {
        return $this->belongsTo(CurriculumCourse::class, 'curriculum_course_id');
    }
}
