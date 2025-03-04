<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('missions')->insert([
            [
                'mission_no' => '1',
                'description' => 'To produce graduates equipped with advanced knowledge and lifelong learning skills with ethical standards through high quality instruction'
            ],
            [
                'mission_no' => '2',
                'description' => 'innovative research'
            ],
            [
                'mission_no' => '3',
                'description' => 'and impactful community engagements.'
            ]
        ]);
    }
}
