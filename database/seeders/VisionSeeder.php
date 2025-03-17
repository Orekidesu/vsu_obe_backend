<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('visions')->insert([
            ['description' => 'A global green university providing progressive leadership in agriculture, science & technology, education and allied fields for societal transformation.']
        ]);
    }
}
