<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurriculumCoursePo extends Model
{
    use HasFactory;

    protected $fillable = [
        'curriculum_course_id',
        'po_id',
        'ird',
    ];

    public function curriculumCourse(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_course_id');
    }
    public function po(): BelongsTo
    {
        return $this->belongsTo(ProgramOutcome::class, 'po_id');
    }
}