<?php

/*
// =====Currently useless since we're using pivot instead =====
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseOutcomePO extends Model
{
    use HasFactory;

    protected $fillable = [
        'co_id',
        'po_id',
        'ied',
    ];

    public function co(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class, 'co_id');
    }

    public function po(): BelongsTo
    {
        return $this->belongsTo(ProgramOutcome::class, 'po_id');
    }
}
    */