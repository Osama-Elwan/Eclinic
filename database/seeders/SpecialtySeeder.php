<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialty;
use Faker\Factory as Faker;


class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {

        $faker = Faker::create();

        // Possible point values to choose from
        $possiblePoints = [2000, 3000, 4000, 5000, 1500, 2500, 6000, 5500];

        // List of specialties to create
        $specialties = [
            'Cardiology',
            'Neurology',
            'Dermatology',
            'Gastroenterology',
            'Orthopedics',
            'Oncology',
            'Pediatrics',
            'Psychiatry',
            'Ophthalmology',
            'Pulmonology',
            'Otolaryngology (ENT)',
            'Nephrology',
            'Urology',
        ];

        // Create each specialty with a random points value
        foreach ($specialties as $name) {
            $randomPoints = $possiblePoints[array_rand($possiblePoints)]; // Select random points

            Specialty::create([
                'name' => $name,
                'points' => $randomPoints,

            ]);
        }
    }
}
