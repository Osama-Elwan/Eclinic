<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;



class LogoutController extends Controller
{


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
