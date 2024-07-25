<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctorId',
        // 'specialtyId',
        'date',
        'start_time',
        'end_time',
        'is_reserved',
    ];

    // Define relationships
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctorId');
    }
    public function patient()
    {
        return $this->belongsTo(User::class, 'patientId');
    }

    // public function specialty()
    // {
    //     return $this->belongsTo(Specialty::class, 'specialtyId');
    // }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'scheduleId');
    }
}
