<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProgramProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'abbreviation',
        'status',
        'version',
        'comment',
        'proposed_by_id',
        'department_revision_required',
        'committee_revision_required'
    ];

    protected $casts = [
        'status' => 'string',
        'version' => 'integer',
        'department_revision_required' => 'boolean',
        'committee_revision_required' => 'boolean'
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by_id');
    }

    public function peos(): HasMany
    {
        return $this->hasMany(ProgramEducationalObjective::class, 'program_proposal_id');
    }

    public function pos(): HasMany
    {
        return $this->hasMany(ProgramOutcome::class, 'program_proposal_id');
    }

    public function curriculum(): HasOne
    {
        return $this->hasOne(Curriculum::class, 'program_proposal_id');
    }

    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class, 'program_proposal_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ProgramProposalRevision::class, 'program_proposal_id');
    }


    public function proposalVersions(): HasMany
    {
        return $this->hasMany(ProgramProposalVersion::class, 'program_proposal_id');
    }


    // in the future, might add submitted by, and reviewed by

}
