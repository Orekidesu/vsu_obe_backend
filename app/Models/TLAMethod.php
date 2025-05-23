<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TLAMethod extends Model
{
    use HasFactory;

    protected $table = 'tla_methods';
    protected $fillable = [
        'co_id',
        'teaching_methods',
        'learning_resources',
    ];
    protected $casts = [
        'teaching_methods' => 'json',
        'learning_resources' => 'json',
    ];

    public function co(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class, 'co_id');
    }
}