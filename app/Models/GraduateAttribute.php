<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GraduateAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'ga_no',
        'name',
        'description',
    ];

    public function peos(): BelongsToMany
    {
        return $this->belongsToMany(ProgramEducationalObjective::class, 'graduate_attribute_peo', 'ga_id', 'peo_id');
    }
    public function pos(): BelongsToMany
    {
        return $this->belongsToMany(ProgramOutcome::class, 'program_outcome_ga', 'ga_id', 'po_id');
    }
}
