<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TLAMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'co_id',
        'teaching_methods',
        'learning_resources',
    ];

    public function co(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class, 'co_id');
    }
}