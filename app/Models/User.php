<?php

namespace App\Models; // Correct namespace for models

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // If you're using Sanctum
use Tymon\JWTAuth\Contracts\JWTSubject; // If you're using JWTAuth
use App\Models\Appointment; // Import related models

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable; // Use necessary traits

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'isDoctor', // Indicates if the user is a doctor
        'phone',
        'otp',
        'last_seen',
        'image', // Add image .ields
        'date_of_birth',
        'gender',
        'blood_group',
        'specialtyId',
        'price',
        'experience',
        'stars',
        'ratings_count',

    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token', // Hide sensitive fields
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Cast password to hashed
        'rating' => 'float:1',
    ];

    // Relationships with Appointments (as doctor)
    public function doctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctorId'); // Relate to 'Appointment' with foreign key 'doctorId'
    }


    // Relationships with Appointments (as doctor)
    public function doctorSpecialties()
    {
        return $this->hasMany(Specialty::class, 'doctorId'); // Relate to 'Appointment' with foreign key 'doctorId'
    }

    // Relationships with Appointments (as patient)
    public function patientAppointments()
    {
        return $this->hasMany(Appointment::class, 'patientId'); // Relate to 'Appointment' with foreign key 'patientId'
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Use the model's key as the JWT identifier
    }

    /**
     * Return a key-value array containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return []; // Customize JWT claims if needed
    }



    public function isOnline()
{
    return $this->last_seen >= now()->subMinutes(5);
}


  /**
     * Set the image for the user.
     *
     * @param  string  $image
     * @return void
     */
//     public function setImage($image)
//     {
//         $this->image = $image;
//         $this->save();
//     }
}
