<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ScheduleController;

use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth')->group( function () {
// //    return $request->user();


// });
// Route::get('/doctor/{doctorId}/patients', [AppointmentController::class, 'getDoctorPatients']);


Route::get('/test-online', function () {
    dd('i am online ^_^');
});


// User Management


    //register

    Route::post('/register', [RegisterController::class, 'register']);

    // Email Verification using OTP
    Route::post('/verify-email', [RegisterController::class, 'verify']);

    // User Login
    Route::post('/login', [LoginController::class, 'login']);

    



    //forget password
    Route::post('forgot-password', [ForgotPasswordController::class, 'forgot']);
    Route::post('validate-otp', [ForgotPasswordController::class, 'validateOtp'])->middleware('throttle:10,1');
    Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:10,1');






        //for try this routes u should add user token after login (dont forget that comment)
     Route::group(['middleware' => ['jwt.auth']], function () {


        //doctor Route
            //doctor schedule Routes
            Route::post('/schedules', [ScheduleController::class, 'store']);
            Route::get('/schedules/{doctorId}', [ScheduleController::class, 'index']);
            //get doctor patients
            Route::get('/doctor/{doctorId}/patients/{date}',[AppointmentController::class, 'getDoctorPatients']);
            //get all doctor patients
            Route::get('/doctor/{doctorId}/patients',[AppointmentController::class,'getAllDoctorPatients'] )
            ->name('doctor-patients.index');


        //specialties Route

        //get all doctors in specialty
        Route::get('/specialties/{specId}', [\App\Http\Controllers\UserController::class, 'getSpecialtiesById']);
        //specialty info
        Route::get('/specialties', [\App\Http\Controllers\UserController::class, 'getSpecialties']);
        //doctor availability its work of u doctor login (login first )
        Route::get('doctors/online', [UserController::class, 'getOnlineDoctors']);
        //get online doctors by specialty
        Route::get('/doctors/specialty/{specId}/online', [UserController::class, 'getOnlineDoctorsBySpecialty']);





// Patient routes

    // Appointment Management

        //create appointment
        Route::post('/appointments', [\App\Http\Controllers\AppointmentController::class, 'createAppointment']);
        //delete appointment
        Route::delete('/patients/{patientId}/appointments/{appointmentId}', [AppointmentController::class, 'deleteAppointmentByIdForPatient']);
        //get appointment status
        Route::get('/appointments/{appointmentId}/status', [AppointmentController::class, 'getAppointmentStatus']);

        //patient Routes

        //get patient doctors
        Route::get('/patient/{patientId}',[AppointmentController::class, 'getPatientDoctors']);
        //get all doctors
        Route::get('/patients/doctors',[UserController::class, 'getAllDoctors']);

        //payment Routes
        Route::get('/payments/{uid}', [\App\Http\Controllers\PaymentController::class, 'index']);
        Route::post('/payments/{uid}', [\App\Http\Controllers\PaymentController::class, 'payment']);










    // Route::post('doctors/{doctorId}/appointments', [AppointmentController::class, 'storeDoctorAppointment']);


    //Users Routes

  //users informations
    Route::get('/getUserInfo/{userId}', [UserController::class,'getUserInfo']);
    //update information
    Route::post('/users/updateInfo/{id}', [UserController::class,'updateUserInfo']);
    //User rating
    Route::post('/users/rate/{patientId}/{doctorId}', [UserController::class, 'rateUser'])->name('rateUser');

    //delete account
    Route::delete('users/{id}/delete-account',[UserController::class,'deleteAccount'] );
    // change password

    Route::post('users/change-password', [UserController::class, 'changePassword']);

    //get image
    Route::get('/image/{userId}', [UserController::class,'getImage']);




//logout
    Route::post('/logout', [LogoutController::class,'logout']);

 });






