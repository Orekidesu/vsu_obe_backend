<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class GraduateAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('graduate_attributes')->insert([
            [
                'ga_no' => '1',
                'name' => 'Knowledge Competence',
                'description' => 'Demonstrate a mastery of the fundamental knowledge and skills required for functioning effectively as a professional in the discipline, and an ability to integrate and apply them effectively to practice in the workplace.'
            ],
            [
                'ga_no' => '2',
                'name' => 'Creativity and Innovation',
                'description' => 'Experiment with new approaches, challenge existing knowledge boundaries and design novel solutions to solve problems.'
            ],
            [
                'ga_no' => '3',
                'name' => 'Critical and Systems Thinking',
                'description' => 'Identify, define, and deal with complex problems pertinent to the future professional practice or daily life through logical, analytical and critical thinking.'
            ],
            [
                'ga_no' => '4',
                'name' => 'Communication',
                'description' => 'Communicate effectively (both orally and in writing) with a wide range of audiences, across a range of professional and personal contexts, in English and Pilipino.'
            ],
            [
                'ga_no' => '5',
                'name' => 'Lifelong Learning',
                'description' => 'Identify own learning needs for professional or personal development; demonstrate an eagerness to take up opportunities for learning new things as well as the ability to learn effectively on their own.'
            ],
            [
                'ga_no' => '6',
                'name' => 'Leadership, teamwork, and Interpersonal Skills',
                'description' => 'Function effectively both as a leader and as a member of a team; motivate and lead a team to work towards goal; work collaboratively with other team members; as well as connect and interact socially and effectively with diverse culture.'
            ],
            [
                'ga_no' => '7',
                'name' => 'Global Outlook',
                'description' => 'Demonstrate an awareness and understanding of global issues and willingness to work, interact effectively and show sensitivity to cultural diversity.'
            ],
            [
                'ga_no' => '8',
                'name' => 'Social and National Responsibility',
                'description' => 'Demonstrate an awareness of their social and national responsibility; engage in activities that contribute to the betterment of the society; and behave ethically and responsibly in social, professional and work environments.'
            ],
        ]);
    }
}
