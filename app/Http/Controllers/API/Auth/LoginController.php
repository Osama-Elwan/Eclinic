<?php

// namespace App\Http\Controllers\API\Auth;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// class LoginController extends Controller
// {
//     //
// }



namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginController extends Controller
{
    public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        // Get the authenticated user
        $user = Auth::user();

        // Get the current time in Egypt
        $egyptTime = Carbon::now('Africa/Cairo');

        // Update the last_seen property with the Egypt time
        $user->last_seen = $egyptTime;
        $user->save();

        // Add the update.last.seen middleware to the auth middleware group
        $request->route()->middleware(['auth', 'update.last.seen']);
    }

    try {
        // Set the TTL to 1 hour
        JWTAuth::factory()->setTTL(60);

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    } catch (JWTException $e) {
        return response()->json(['error' => 'Could not create token'], 500);
    }

    return response()->json(['token' => $token , 'success'=> true], 200);
}
}
