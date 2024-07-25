<?php

// namespace App\Http\Controllers\API\Auth;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// class RegisterController extends Controller
// {
//     //
// }

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Validator; // Import the Validator class





class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Validate request
        // $request->validate([
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|string|email|unique:users|max:255',
        //     // 'username' => 'required|string|unique:users|max:255',
        //     'password' => 'required|string|min:8|confirmed',
        //     'phone' => 'required|string|max:20',
        //     'isDoctor' => 'nullable|boolean', // Add validation rule for isDoctor field

        // ]);
        // Validate request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'isDoctor' => 'nullable|boolean', // Add validation rule for isDoctor field
        ]);

        // Check if validation fails
//         if ($validator->fails()) {
//             return response()->json(['error' => $validator->errors()], 400);
//         }
    if($validator->fails()){
        return response()->json(['error'=>$validator->errors()],400);
    }


    // dd("helloz");

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            // 'username' => $request->username,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'isDoctor' => $request->isDoctor ?? false, // Set isDoctor to false if not provided

        ]);

        // Generate OTP
        $otp = rand(100000, 999999); // Generate a 6-digit OTP

        // Save OTP to database
        $user->otp = $otp;
        $user->save();

        // Send OTP via email
        Mail::to($user->email)->send(new VerifyEmail($otp));

        // Return response
        return response()->json(['message' => 'User registered successfully. OTP sent to email for verification'], 201);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'otp' => 'required|digits:6',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and OTP matches
        if (!$user || $user->otp != $request->otp) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        // Update user account as verified
        $user->email_verified_at = now();
        $user->otp = null;
        $user->save();

        return response()->json(['message' => 'Email verified successfully'], 200);
    }
}
