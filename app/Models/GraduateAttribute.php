<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GraduateAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'ga_no',
        'name',
        'description',
    ];
}
