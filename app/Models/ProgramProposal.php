<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProgramProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'abbreviation',
        'status',
        'version',
        'comment'
    ];

    protected $casts = [
        'status' => 'string',
        'version' => 'integer'
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }


    // in the future, might add submitted by, and reviewed by


}