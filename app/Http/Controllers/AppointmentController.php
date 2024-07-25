<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Support\Facades\Validator;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\ModelNotFoundException;




use Illuminate\Http\Exceptions\HttpResponseException;

class AppointmentController extends Controller
{


public function createAppointment(Request $request)
{
    // Validate the request data
    $validatedData = $request->validate([
        'patientId' => 'required|exists:users,id',
        'doctorId' => 'required|exists:users,id',
        // 'specialtyId' => 'required|exists:specialties,id',
        'appointmentDate' => 'required|date',
        'appointmentTime' => 'required|date_format:H:i:s',
    ]);

    // Find the patient and doctor
    $patient = \App\Models\User::find($validatedData['patientId']);
    $doctor = \App\Models\User::findOrFail($validatedData['doctorId']);

    // Validate user types
    if (!$patient || $patient->isDoctor) {
        return response()->json(['message' => 'Patient Not Found or Invalid'], 424);
    }

    if (!$doctor || !$doctor->isDoctor) {
        return response()->json(['message' => 'Doctor Not Found or Invalid'], 422);
    }

    // Calculate points to transfer from patient to doctor
    // $pointsPerDollar = 10; // 1 dollar = 10 points
    $pointsToTransfer =$doctor->price ;

    // Check if patient has enough points
    if ($patient->points < $pointsToTransfer) {
        return response()->json(['message' => 'Patient does not have enough points'], 422);
    }

    try {
        DB::beginTransaction();

        // Deduct points from patient
        $patient->points -= $pointsToTransfer;
        $patient->save();

        // Add points to doctor
        $doctor->points += $pointsToTransfer;
        $doctor->p_counter =$doctor->p_counter +1 ;
        $doctor->save();

        // Retrieve the schedule
        $schedule = \App\Models\Schedule::where('doctorId', $validatedData['doctorId'])
                    ->where('date', $validatedData['appointmentDate'])
                    ->where('start_time', $validatedData['appointmentTime'])
                    // ->where('specialtyId', $validatedData['specialtyId'])
                    ->first();

        if (!$schedule) {
            DB::rollBack();
            return response()->json(['message' => 'Schedule Not Found'], 422);
        }

        // Check if the schedule is not reserved
        if ($schedule->is_reserved) {
            DB::rollBack();
            return response()->json(['message' => 'Invalid or already reserved schedule'], 422);
        }

        // Create the appointment
        $appointment = \App\Models\Appointment::create([
            'patientId' => $validatedData['patientId'],
            'doctorId' => $validatedData['doctorId'],
            // 'specialtyId' => $validatedData['specialtyId'],
            'scheduleId' => $schedule->id,
            'appointmentDate' => $schedule->date,
            'appointmentTime' => $schedule->start_time,
        ]);

        // Mark the schedule as reserved
        $schedule->is_reserved = true;
        $schedule->save();

        DB::commit();

        // Return success response
        return response()->json(['message' => 'Appointment created successfully', 'appointment' => $appointment], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'An error occurred while creating the appointment', 'error' => $e->getMessage()], 500);
    }
}



//final result
public function getDoctorPatients($doctorId, $date)
{
    try {
        // Validate doctorId
        $doctor = User::findOrFail($doctorId);
        if (!$doctor->isDoctor) {
            return response()->json([
                'message' => 'Invalid doctor ID'
            ], 400);
        }

        // Find appointments for the doctor on the given date
        $appointments = Appointment::where('doctorId', $doctorId)
            ->whereDate('appointmentDate', $date)
            ->select('doctorId', 'patientId', 'appointmentDate', 'appointmentTime')
            ->with('patient')
            ->orderBy('appointmentTime')
            ->get();

        // Extract unique patients from the appointments
        $patients = $appointments->pluck('patient')->unique();

        // Check if there are any patients for the given date
        if ($patients->isEmpty()) {
            return response()->json([
                'message' => 'No patients for this date'
            ], 404);
        }

        // Validate patientId if provided
        if ($patientId = request()->query('patientId')) {
            $patient = User::find($patientId);
            if (!$patient || !$patient->isPatient) {
                return response()->json([
                    'message' => 'Invalid patient ID'
                ], 400);
            }

            // Filter appointments by patientId
            $appointments = $appointments->filter(function ($appointment) use ($patientId) {
                return $appointment->patientId == $patientId;
            });

            // Extract unique patient from the filtered appointments
            $patients = $appointments->pluck('patient')->unique();
        }

        return response()->json([
            'doctor' => $doctor->name,
            'patients' => $patients,
            'appointments' => $appointments
        ]);
    } catch (ModelNotFoundException $e) {
        // Return a JSON response with a 404 status code
        return response()->json([
            'message' => 'Doctor not found'
        ], 404);
    }
}






public function getAppointmentStatus($appointmentId)
{
    // Retrieve the appointment
    $appointment = Appointment::findOrFail($appointmentId);
    $schedule = Schedule::find($appointment->scheduleId);
    // dd($schedule);
    // $endTime = $schedule->end_time;
    
    // Combine the appointment time and date into a Carbon instance
    $appointmentDateTime = Carbon::parse($appointment->appointmentDate . ' ' . $appointment->appointmentTime);
    $appointmentDateTime->tz('Africa/Cairo');
    $appointmentDateTime->subHours(3);
    $scheduleDateTime = Carbon::parse($schedule->date . ' ' . $schedule->end_time);
    $scheduleDateTime->tz('Africa/Cairo');
    $scheduleDateTime->subHours(3);
    
    // Get the current time
    $currentTime = Carbon::now('Africa/Cairo');

    if($currentTime->lt($scheduleDateTime)) {
        if($currentTime->gt($appointmentDateTime) && $currentTime->lt($scheduleDateTime)){
            $status = 1;
        $statusString = "onGoing";
        }else if($appointmentDateTime->gt($currentTime)){
            $status = 2;
        $statusString = "upcoming";
        }
    }else {
        // The appointment is complete
        $status = 3;
        $statusString = "complete";
    }
    // dd($currentTime->gt($appointmentDateTime) && $currentTime->lt($scheduleDateTime));
    // // Compare the appointment time with the current time
    // if ($appointmentDateTime->gt($currentTime) || $currentTime->lt($scheduleDateTime)) {
    //     // The appointment is upcoming
    //     $status = true;
    //     $statusString = "upcoming";
    // } else {
    //     // The appointment is complete
    //     $status = false;
    //     $statusString = "complete";
    // }

    // Return the appointment status as a JSON response
    return response()->json([
        'status' => $status,
        'statusString'=>$statusString

    ]);
}



public function getPatientDoctors($patientId)
{
    // Validate the patient ID
    $patient = User::find($patientId);
    if (!$patient) {
        return response()->json(['message' => 'Patient not found'], 404);
    }

    // Get the appointments for the patient
    $appointments = Appointment::where('patientId', $patientId)
        ->with('doctor')
        ->orderBy('appointmentDate')
        ->orderBy('appointmentTime')
        ->get();

    // Get the status of each appointment
    foreach ($appointments as $appointment) {
        $appointment->status = $this->getAppointmentStatus($appointment->id);
        // $appointment->statusString = $this->getAppointmentStatus($appointment->id);
    }

    // Return the patient's doctors and appointments
    return response()->json([
        'patientName' => $patient->name,
        'patientId' => $patient->id,
        // 'doctors' => $appointments->unique('doctorId')->values('doctor'),
        'appointments' => $appointments,
    ]);
}

//delete appointment


public function deleteAppointmentByIdForPatient(int $patientId, int $appointmentId)
    {
        $patient = User::find($patientId);

        if ($patient) {
            $appointment = Appointment::find($appointmentId);
            $doctorId = $appointment->doctorId;
            $doctor = User::find($doctorId);


            if ($appointment) {
                $doctor->p_counter =$doctor->p_counter -1 ;
                $doctor->save();
                $appointment->delete();
                return response()->json(['message' => 'Appointment deleted successfully.'], Response::HTTP_OK);
            } else {
                return response()->json(['message' => 'Appointment not found or unable to delete.'], Response::HTTP_NOT_FOUND);
            }
        } else {
            return response()->json(['message' => 'Patient not found.'], Response::HTTP_NOT_FOUND);
        }
    }


    public function getAllDoctorPatients($doctorId)
{
    $patients = Appointment::where('doctorId', $doctorId)
                            ->pluck('patientId')
                            ->unique()
                            ->sortBy(fn ($patientId) => $patientId)
                            ->map(function ($patientId) {
                                return User::findOrFail($patientId);
                            });

                          $counterz =  $patients->count();

    return response()->json(
        [
            "meta" => $patients,
            "total" => $counterz
        ]
    );
}


}
