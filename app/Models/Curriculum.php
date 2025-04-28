<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    use HasFactory;
    protected $fillable = [
        'program_id',
        'name',
    ];

    public function programProposal(): BelongsTo
    {
        return $this->belongsTo(ProgramProposal::class, 'program_proposal_id');
    }
    public function curriculumCourses(): HasMany
    {
        return $this->hasMany(CurriculumCourse::class, 'curriculum_id');
    }
}
