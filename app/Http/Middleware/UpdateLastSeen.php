<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
{
    if (Auth::check()) {
        // Get the authenticated user
        $user = Auth::user();

        // Get the current time in Egypt
        $egyptTime = Carbon::now('Africa/Cairo');

        // Update the last_seen property with the Egypt time
        $user->last_seen = $egyptTime;
        $user->save();
    }

    return $next($request);
}
}