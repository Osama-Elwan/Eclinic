<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Schedule;
use App\Models\Specialty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{

    public function deleteAccount(Request $request, $id)
{
    $user = User::findOrFail($id);

    // If you want to check if the user is a doctor or patient, you can do:
    if ($user->is_doctor) {
        $appointments = Appointment::where('doctorId', $id)->get();
    } else {
        $appointments = Appointment::where('patientId', $id)->get();
    }

    foreach ($appointments as $appointment) {
        $getSchedule=$appointment->scheduleId;
        $scheduleChange = Schedule::find($getSchedule);
        $scheduleChange->is_reserved =0;
        $scheduleChange->save();
        $appointment->delete();
    }

    $user->delete();

    return response()->json(['message' => 'Account deleted successfully']);
}









public function getAllDoctors()
{
    // Get all users with isDoctor = 1
    $doctors = User::where('isDoctor', 1)->get();

    // Return the doctors as a JSON response
    return response()->json($doctors);
}









public function changePassword(Request $request)
{
    // Retrieve the authenticated user
    $user = $request->user();

    try {
        $this->validate($request, [
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        // Validate the current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            throw new \Exception('Current password is incorrect.');
        }

        // Hash the new password
        $user->password = Hash::make($request->input('new_password'));

        // Save the updated user
        $user->save();

        return response()->json(['message' => 'Password changed successfully.']);

    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 400);
    }
}


public function getOnlineDoctors()
{
    $currentTime = Carbon::now('Africa/Cairo');
    $onlineDoctors = User::where('isDoctor', true)
                        ->where('last_seen', '>=', $currentTime->subHour(1))
                        ->get();

    $offlineDoctors = User::where('isDoctor', true)
                        ->where('last_seen', '<', $currentTime->subHour(1))

                        ->get();

    $doctors = $onlineDoctors->merge($offlineDoctors);

    $doctorsArray = [];

    foreach ($doctors as $doctor) {
        $doctorsArray[] = [
            // 'id' => $doctor->id,
            // 'name' => $doctor->name,
            'doctor'=>$doctor,
            'is_online' => $doctor->last_seen >= $currentTime->subHour(1)
        ];
    }

    return response()->json(['doctors' => $doctorsArray], 200);
}



//user information


//get information


public function getUserInfo(Request $request, $userId)
{
    $user = User::find($userId);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    $userInfo = [
        'userId' => $user->id,
        'image' => url('items/image/' . $user->image),
        'name' => $user->name,
        'date_of_birth' => $user->date_of_birth,
        'gender' => $user->gender,
        'blood_group' => $user->blood_group,
        'phone' => $user->phone,
        'price' => $user->price,
        'email' => $user->email,
        'specialtyId' => $user->isDoctor === 1 ? $user->specialtyId : null,
        'isDoctor' => $user->isDoctor,
        'experience'=>$user->experience,
        'stars' =>$user->stars,
        'points' =>$user->points,
        'p_counter' =>$user->p_counter,
        'ratings_count' =>$user->ratings_count,
    ];

    return response()->json($userInfo, 200);
}




public function rateUser(Request $request, $patientId, $doctorId)
{
    $validateData = Validator::make($request->all(), [
        'stars' => 'required|numeric|min:0|max:5',
    ]);

    if ($validateData->fails()) {
        $data['errors'] = $validateData->errors();
        $data['status'] = '403';
        return response()->json($data,  403);
    }

    $user = User::find($patientId);
    if (!$user) {
        $data['msg'] = 'User not found';
        $data['status'] = 404;
        $data['data'] = null;
        return response()->json($data, 404);
    }

    $ratedUser = User::find($doctorId);
    if (!$ratedUser) {
        $data['msg'] = 'User being rated not found';
        $data['status'] = 404;
        $data['data'] = null;
        return response()->json($data, 404);
    }

    $rating = $request->input('stars');
    $ratedUser->stars = ($ratedUser->ratings_count > 0)? ($ratedUser->stars * $ratedUser->ratings_count + $rating) / ($ratedUser->ratings_count + 1) : $rating;
    $ratedUser->ratings_count++;

    $ratedUser->save();

    $data['msg'] = 'Rating saved successfully';
    $data['status'] = 200;
    $data['data'] = $ratedUser;
    return response()->json($data, 200);
}
















/**
     * Update a user's information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */







public function updateUserInfo(Request $request, $id)
{
    if (!$request->has('specialtyId')) {
        $request->merge(['specialtyId' => 1]);
    }

    $validateData = Validator::make($request->all(), [
        'name' => 'string|max:255',
        'gender' => 'string|in:Male,Female,other',
        'image' => 'image|mimes:png,jpeg,jpg,gif|max:4080',
        'date_of_birth' => 'date',
        'blood_group' => 'string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        'phone' => 'string|max:20',
        'specialtyId' => 'sometimes|nullable|integer|exists:specialties,id',
        'price' => 'integer',
        'experience' => 'sometimes|nullable|integer|max:255',
    ]);

    if ($validateData->fails()) {
        $data['errors'] = $validateData->errors();
        $data['status'] = '403';
        return response()->json($data,  403);
    }

    $update = User::find($id);
    if ($update) {

        if ($request->hasFile('image')) {
            $image = $request->image;
            $imageName = time() . "_" . rand(0, 1000) . "." . $image->extension();
            $image->move(base_path('./public/items/image'), $imageName);
            $update->image = $imageName;
        } else {
            $update->image = $update->image;
        }

        $update->name = $request->filled('name') ? $request->name : $update->name;
        $update->gender = $request->filled('gender') ? $request->gender : $update->gender;
        $update->date_of_birth = $request->filled('date_of_birth') ? $request->date_of_birth : $update->date_of_birth;
        $update->blood_group = $request->filled('blood_group') ? $request->blood_group : $update->blood_group;
        $update->phone = $request->filled('phone') ? $request->phone : $update->phone;
        $update->specialtyId = $request->filled('specialtyId') ? $request->specialtyId : $update->specialtyId;
        $update->price = $request->filled('price') ? $request->price : $update->price;
        $update->experience = $request->filled('experience') ? $request->experience : $update->experience;

        $update->save();
        $update->image = url('items/image/' . $update->image);
        $data["data"] = $update;
        $data["status"] = "200";
        return response()->json($data, 200);
    } else {
        $data['msg'] = 'Item not found';
        $data['status'] = 404;
        $data['data'] = null;
        return response()->json($data, 404);
    }
}























// Additional methods for fetching specialties
public function getSpecialties()
{
    $specialties = Specialty::all(); // Fetch all specialties

    return response()->json(['specialties' => $specialties], 200);
}



public function getSpecialtiesById($specId)
    {
        // Query users where isDoctor is true and specialtyId matches $specId
        $doctors = User::where('isDoctor', true)
                       ->where('specialtyId', $specId)
                       ->get();

        return response()->json(['doctors' => $doctors], 200);
    }



  public function getOnlineDoctorsBySpecialty($specId)
{
    $currentTime = Carbon::now('Africa/Cairo');

    $doctors = User::where('isDoctor', true)
                   ->where('specialtyId', $specId)
                   ->where('last_seen', '>=', $currentTime->subHour(1))
                   ->get();

    $onlineDoctorsArray = [];

    foreach ($doctors as $doctor) {
        $onlineDoctorsArray[] = [
            'doctor' => $doctor,
            'is_online' => true
        ];
    }

    return response()->json(['doctors' => $onlineDoctorsArray], 200);
}



public function getImage($userId){
    $user = User::find($userId);
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    $userImage = [
        'image' => url('items/image/' . $user->image),
    ];
    return response()->json($userImage, 200);
}

public function logout(Request $request)
{
    $token = JWTAuth::getToken();

    try {
        JWTAuth::setToken($token)->invalidate();

        // Remove the last_seen attribute from the user
        $user = $request->user();
        $user->last_seen = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully',
        ]);
    } catch (TokenInvalidException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid token provided',
        ], 401);
    }
}



}



