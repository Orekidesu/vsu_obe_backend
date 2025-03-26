<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProgramEducationalObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'statement',
    ];

    // protected $with = ['program'];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(Mission::class, 'program_educational_objective_mission', 'peo_id', 'mission_id');
    }

    public function gas(): BelongsToMany
    {
        return $this->belongsToMany(GraduateAttribute::class, 'graduate_attribute_peo', 'peo_id', 'ga_id');
    }

    public function pos(): BelongsToMany
    {
        return $this->belongsToMany(ProgramOutcome::class, 'program_outcome_peo', 'peo_id', 'po_id');
    }
}
