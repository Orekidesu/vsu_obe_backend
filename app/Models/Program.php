<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'abbreviation',
    ];

    // now whenever we query a particular program, it always eager load the department it associated with
    protected $with = ['department'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function programEducationalObjective(): HasMany
    {
        return $this->hasMany(ProgramEducationalObjective::class, 'program_id');
    }
}
