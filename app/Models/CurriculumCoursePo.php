<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurriculumCoursePo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'curriculum_course_po';

    protected $fillable = [
        'curriculum_course_id',
        'po_id',
        'ird',
    ];

    public function curriculumCourse(): BelongsTo
    {
        return $this->belongsTo(CurriculumCourse::class, 'curriculum_course_id');
    }
    
    public function po(): BelongsTo
    {
        return $this->belongsTo(ProgramOutcome::class, 'po_id');
    }
}