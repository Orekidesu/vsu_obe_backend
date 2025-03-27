<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    public function peos(): BelongsToMany
    {
        return $this->belongsToMany(ProgramEducationalObjective::class, 'program_outcome_peo', 'po_id', 'peo_id');
    }
    public function gas(): BelongsToMany
    {
        return $this->belongsToMany(GraduateAttribute::class, 'program_outcome_ga', 'po_id', 'ga_id');
    }
}
