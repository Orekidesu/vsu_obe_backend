<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'statement',
    ];

    // protected $with = [
    //     'program'
    // ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
}