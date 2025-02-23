<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('departments')->insert([
            [
                'name' => 'Department of Civil Engineering',
                'abbreviation' => 'DCE',
                'faculty_id' => '1'

            ],
            [
                'name' => 'Department of Agricultural and Biosystems Engineering',
                'abbreviation' => 'DABE',
                'faculty_id' => '1'

            ],
            [
                'name' => 'Department of Computer Science and Technology',
                'abbreviation' => 'DCST',
                'faculty_id' => '2'

            ],
        ]);
    }
}
