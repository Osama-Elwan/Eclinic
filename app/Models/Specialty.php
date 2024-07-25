<?php

namespace App\Models; // Correct namespace for models

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment; // Import related models
use Carbon\Carbon; // Carbon for date/time handling

class Specialty extends Model
{
    use HasFactory; // Include factory trait if using Laravel factories

    // Fields that can be mass-assigned
    protected $fillable = ['name', 'points']; 

    // Relationships with Appointments
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'specialtyId'); // Foreign key 'specialtyId'
    }


    // Relationship with User (doctor)
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctorId'); // Relate to 'User' with foreign key 'doctorId'
    }
}