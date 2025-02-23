<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('faculties')->insert([
            [
                'name' => 'Faculty of Engineering',
                'abbreviation' => "FE"
            ],
            [
                'name' => 'Faculty of Computing',
                'abbreviation' => 'FC',
            ],
            [
                'name' => 'Faculty of Agriculture',
                'abbreviation' => 'FA',
            ]

        ]);
    }
}
