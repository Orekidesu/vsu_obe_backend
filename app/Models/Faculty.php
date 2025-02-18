<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Faculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
    ];


    public function user():HasMany
    {
        return $this->hasMany(User::class,'faculty_id');
    }

    public function department():HasMany
    {
        return $this->hasMany(Department::class,'department_id');
    }
}