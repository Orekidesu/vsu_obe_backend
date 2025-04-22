<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'sem',
    ];

    public const FIRST = 'first';
    public const SECOND = 'second';
    public const MIDYEAR = 'midyear';
    /**
     * Get all valid semester names
     * 
     * @return array
     */

    public static function getValidSemesterNames(): array
    {
        return [
            self::FIRST,
            self::SECOND,
            self::MIDYEAR,
        ];
    }
}
