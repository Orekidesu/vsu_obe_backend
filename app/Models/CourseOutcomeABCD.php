<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOutcomeABCD extends Model
{
    use HasFactory;

    protected $table = 'course_outcome_abcd';

    protected $fillable = [
        'co_id',
        'audience',
        'behavior',
        'condition',
        'degree',
    ];

    public function co(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class, 'co_id');
    }
}