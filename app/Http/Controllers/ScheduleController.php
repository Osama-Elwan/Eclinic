<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Fetch schedules for a specific doctor.
     *
     * @param  int  $doctorId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($doctorId)
    {
        // Ensure the authenticated user is the doctor requesting the schedules
        if (Auth::id() != $doctorId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $schedules = Schedule::where('doctorId', $doctorId)->get();

        return response()->json([
            'schedules' => $schedules,
        ]);
    }

    /**
     * Store a new schedule.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
     public function store(Request $request)
{
    $request->validate([
        'doctorId' => 'required|exists:users,id,isDoctor,1',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'start_time' => 'required|date_format:H:i:s',
        'end_time' => 'required|date_format:H:i:s|after:start_time',
        // 'specialtyId' => 'required|exists:specialties,id',
    ]);

    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);

    $dates = [];

    for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
        $dates[] = $date->format('Y-m-d');
    }

    foreach ($dates as $date) {
        // Check if the slot is already reserved
        $conflictingSchedule = Schedule::where('doctorId', $request->doctorId)
            ->where('date', $date)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->start_time)
                        ->where('end_time', '>', $request->start_time);
                })->orWhere(function ($q) use ($request) {
                    $q->where('start_time', '>=', $request->start_time)
                        ->where('start_time', '<', $request->end_time);
                });
            })
            ->exists();

        if ($conflictingSchedule) {
            return response()->json(['error' => 'This slot is already reserved'], 409);
        }

        $schedule = Schedule::create([
            'doctorId' => $request->doctorId,
            'date' => $date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            // 'specialtyId' => $request->specialtyId,
            'is_reserved' => false,
        ]);
    }

    return response()->json([
        'message' => 'Schedules created successfully',
    ], 201);
}
}
