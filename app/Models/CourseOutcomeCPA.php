<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOutcomeCPA extends Model
{
    use HasFactory;

    protected $table = 'course_outcome_cpa';

    protected $fillable = [
        'co_id',
        'cpa',
    ];

    protected $casts = [
        'cpa' => 'json',
    ];

    public function co(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class, 'co_id');
    }
}