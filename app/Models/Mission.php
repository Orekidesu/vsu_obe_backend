<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mission extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_no',
        'description',
    ];

    public function programEducationalObjectives(): BelongsToMany
    {
        return $this->belongsToMany(ProgramEducationalObjective::class, 'program_educational_objective_mission', 'mission_id', 'peo_id');
    }
}
