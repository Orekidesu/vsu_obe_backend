<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramProposalVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_proposal_id',
        'version',
        'change_type',
    ];


    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ProgramProposal::class, 'program_proposal_id');
    }

    public function proposalVersionDetails(): HasMany
    {
        return $this->hasMany(ProgramProposalVersionDetail::class, 'program_proposal_version_id');
    }
}
