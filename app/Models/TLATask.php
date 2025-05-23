<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TLATask extends Model
{
    use HasFactory;

    protected $table = 'tla_tasks';
    protected $fillable = [
        'co_id',
        'at_code',
        'at_name',
        'at_tool',
        'weight',
    ];

    public function co(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class, 'co_id');
    }
}