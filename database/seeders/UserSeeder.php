<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\User; // Correct import for User model
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // // Seed doctors
        // for ($i = 0; $i < 5; $i++) { // 5 sample doctors
        //     User::create([
        //         'name' => $faker->name,
        //         'email' => $faker->unique()->safeEmail,
        //         'password' => Hash::make('password'), // Default password
        //         'isDoctor' => true, // Mark as doctor
        //         'phone' => $faker->phoneNumber,
        //         'price' => $faker->randomDigit,
        //         'points' => $faker->numberBetween($min = 1500, $max = 6000),
        //     ]);
        // }

        // // Seed patients
        // for ($i = 0; $i < 10; $i++) { // 10 sample patients
        //     User::create([
        //         'name' => $faker->name,
        //         'email' => $faker->unique()->safeEmail,
        //         'password' => Hash::make('password'), // Default password
        //         'isDoctor' => false, // Mark as patient
        //         'phone' => $faker->phoneNumber,
        //         'price' => $faker->randomDigit,
        //         'points' => $faker->numberBetween($min = 1500, $max = 6000),

        //     ]);
        // }

        // Seed doctors
for ($i = 0; $i < 5; $i++) { // 5 sample doctors
    User::create([
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make('password'), // Default password
        'isDoctor' => true, // Mark as doctor
        'phone' => $faker->phoneNumber,
        'price' => $faker->randomFloat(2, 0, 1000), // Generates a float number between 0 and 1000 with 2 decimal places
        'points' => $faker->numberBetween($min = 1500, $max = 6000),
        'specialtyId' => $faker->numberBetween(1, 13),
    ]);
}

// Seed patients
for ($i = 0; $i < 10; $i++) { // 10 sample patients
    User::create([
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => Hash::make('password'), // Default password
        'isDoctor' => false, // Mark as patient
        'phone' => $faker->phoneNumber,
        // 'price' => $faker->randomFloat(2, 0, 1000), // Generates a float number between 0 and 1000 with 2 decimal places
        'points' => $faker->numberBetween($min = 1500, $max = 6000),


    ]);
}
    }
}
