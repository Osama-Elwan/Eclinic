<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class ForgotPasswordController extends Controller
{
    /**
     * Send password reset OTP to the user's email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */


    /**
     * Reset user's password using OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function reset(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email',
    //         'otp' => 'required|digits:6',
    //         'password' => 'required|string|min:8|confirmed',
    //     ]);

    //     $reset = DB::table('password_resets')->where('email', $request->email)->first();

    //     if (!$reset || $reset->otp != $request->otp) {
    //         return response()->json(['error' => 'Invalid OTP'], 400);
    //     }

    //     $user = User::where('email', $request->email)->firstOrFail();
    //     $user->update(['password' => bcrypt($request->password)]);

    //     DB::table('password_resets')->where('email', $request->email)->delete();

    //     return response()->json(['message' => 'Password reset successfully']);
    // }
    // public function reset(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email|exists:users,email', // Check if email exists in the users table
    //         'otp' => 'required|digits:6',
    //         'password' => 'required|string|min:8|confirmed',
    //     ]);

    //     $reset = DB::table('password_resets')->where('email', $request->email)->first();

    //     if (!$reset || $reset->otp != $request->otp) {
    //         return response()->json(['error' => 'Invalid OTP'], 400);
    //     }

    //     $user = User::where('email', $request->email)->firstOrFail();
    //     $user->update(['password' => Hash::make($request->password)]);

    //     DB::table('password_resets')->where('email', $request->email)->delete();

    //     return response()->json(['message' => 'Password reset successfully']);
    // }
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;
        $otp = rand(100000, 999999);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            ['email' => $email, 'otp' => $otp, 'created_at' => now()]
        );

        Mail::send('emails.reset', ['otp' => $otp], function ($message) use ($email) {
            $message->to($email)->subject('Reset Password OTP');
        });

        return response()->json(['message' => 'Password reset OTP sent to your email']);
    }

    public function validateOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
        ]);

        $reset = DB::table('password_resets')->where('email', $request->email)->first();

        if (!$reset || $reset->otp != $request->otp) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        DB::table('password_resets')->where('email', $request->email)->update(['used' => true]);

        return response()->json(['message' => 'OTP validated successfully']);
    }
    public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $reset = DB::table('password_resets')->where('email', $request->email)->first();

    if (!$reset || $reset->used == false) {
        return response()->json(['error' => 'Invalid OTP'], 400);
    }

    $user = User::where('email', $request->email)->firstOrFail();
    $user->update(['password' => Hash::make($request->password)]);

    DB::table('password_resets')->where('email', $request->email)->delete();

    return response()->json(['message' => 'Password reset successfully']);
}

}
