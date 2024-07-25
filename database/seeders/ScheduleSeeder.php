<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\Specialty;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;


class ScheduleSeeder extends Seeder
{


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $doctorIdRange = range(1, 5); // Example range, adjust as needed

        // Get a doctor user where isDoctor is true
        $doctor = User::where('isDoctor', true)->whereIn('id', $doctorIdRange)->get();

        // If no doctor is found, exit gracefully or handle as per your application's logic
        if (!$doctor) {
            echo "No doctor found with isDoctor = true. Seeder aborted.\n";
            return;
        }

        // Example data for schedules with 30-minute slots
        $startDate = Carbon::now()->addDays(1); // Start generating from tomorrow

        $schedules = [];

        // Create schedules for 30-minute slots from 09:00 to 17:00
        $startTime = Carbon::parse('09:00:00');
        $endTime = Carbon::parse('17:00:00');

        while ($startTime->lt($endTime)) {
            $endTimeSlot = $startTime->copy()->addMinutes(30);

            $schedules[] = [
                'doctorId' => $faker->numberBetween(1, 5),
                // 'specialtyId' => Specialty::inRandomOrder()->first()->id,
                'date' => $startDate->format('Y-m-d'),
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTimeSlot->format('H:i:s'),
                'is_reserved' => $faker->numberBetween(0 , 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $startTime->addMinutes(30);
        }

        // Insert data into the database
        foreach ($schedules as $schedule) {
            Schedule::create($schedule);
        }
    }
}