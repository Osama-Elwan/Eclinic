<?php


namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\User; // Correct import for User model
use App\Models\Specialty; // Correct import for Specialty model
use App\Models\Appointment; // Correct import for Appointment model
use App\Models\Schedule; // Correct import for Appointment model

use Faker\Factory as Faker;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Fetch sample doctors and patients from the User model
        $doctors = User::where('isDoctor', 1)->get(); // Fetch doctors
        $patients = User::where('isDoctor', 0)->get(); // Fetch patients
        
        // Ensure collections are not empty
        if ($doctors->isEmpty() || $patients->isEmpty()) {
            throw new \Exception("Doctors or Patients data is missing. Please seed them first.");
        }
    
        // Fetch specialties
        $specialties = Specialty::all();
        $schedules   = Schedule::all();
    
        if ($specialties->isEmpty()) {
            throw new \Exception("Specialties data is missing. Please seed them first.");
        }
    
        // Use Faker to generate random dates and times
        $faker = Faker::create();
    
        // Create sample appointments
        for ($i = 0; $i < 20; $i++) {
            // Select a random doctor, patient, and specialty only if collections are not empty
            $randomDoctor = $doctors->random(); // Random doctor
            $randomPatient = $patients->random(); // Random patient
            // $randomSpecialty = $specialties->random(); // Random specialty
            $randomSchedule = $schedules->random(); // Random specialty

            $randomDate = Carbon::now()->addDays($faker->numberBetween(1, 30)); // Random date
            $randomTime = $faker->time('H:i:s'); // Random time
    
            // Create the appointment
            Appointment::create([
                'doctorId'    => $randomDoctor->id,
                'patientId'   => $randomPatient->id,
                // 'specialtyId' => $randomSpecialty->id,
                'scheduleId'  => $randomSchedule->id,
                'appointmentDate' => $randomDate->toDateString(),
                'appointmentTime' => $randomTime,
                'isCompleted' => false
            ]);
        }
    }
}
