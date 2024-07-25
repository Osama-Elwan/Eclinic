<?php

namespace App\Models; // Correct namespace for models

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User; // Import related models
use App\Models\Specialty; // Import related models
use App\Models\schedule; // Import related models

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['doctorId', 'patientId'
    , 'appointmentDate', 'appointmentTime',
     'isCompleted', 'scheduleId'];

    // Relationship with User (doctor)
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctorId'); // Relate to 'User' with foreign key 'doctorId'
    }

    // Relationship with User (patient)
    public function patient()
    {
        return $this->belongsTo(User::class, 'patientId'); // Relate to 'User' with foreign key 'patientId'
    }

    // // Relationship with Specialty
    // public function specialty()
    // {
    //     return $this->belongsTo(Specialty::class, 'specialtyId'); // Relate to 'Specialty' with foreign key 'specialtyId'
    // }
    // Relationship with Specialty
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'scheduleId'); // Relate to 'Specialty' with foreign key 'specialtyId'
    }
}
