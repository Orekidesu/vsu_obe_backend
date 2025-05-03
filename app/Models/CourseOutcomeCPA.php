<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOutcomeCPA extends Model
{
    use HasFactory;

    protected $fillable = [
        'co_id',
        'cpa',
    ];

    public function co(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class, 'co_id');
    }
}