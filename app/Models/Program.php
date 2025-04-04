<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'abbreviation',
        'status',
        'version',
    ];

    // now whenever we query a particular program, it always eager load the department it associated with
    // only use with when the relationship is belongs so or hasOne 
    // don't use it if the table has multiple table connected to it
    // protected $with = ['department'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function programEducationalObjective(): HasMany
    {
        return $this->hasMany(ProgramEducationalObjective::class, 'program_id');
    }

    public function proposal(): HasOne
    {
        return $this->hasOne(ProgramProposal::class, 'program_id');
    }

    public function curriculum(): HasOne
    {
        return $this->hasOne(Curriculum::class, 'program_id');
    }
}
