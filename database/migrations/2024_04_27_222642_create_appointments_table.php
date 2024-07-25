<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('doctorId') // Foreign key to User (doctors)
                ->constrained('users') // References 'users' table
                ->onDelete('cascade'); // Delete appointments if user is deleted
            // $table->foreignId('specialtyId') // Foreign key to Specialty
            //     ->constrained('specialties')
            //     ->onDelete('cascade');
            $table->foreignId('patientId') // Foreign key to User (patients)
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('scheduleId') // Foreign key to User (patients)
                ->constrained('schedules')
                ->onDelete('cascade');
            $table->boolean('isCompleted')->default(false);
            $table->date('appointmentDate'); // Appointment date
            $table->time('appointmentTime'); // Appointment time
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
