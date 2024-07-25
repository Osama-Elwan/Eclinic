<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($uid)
    {
        // Find payments by user ID (uid)
        $payments = Payment::where('uid', $uid)->get()->last();

        return response()->json(['payments' => $payments], 200);
    }








// public function payment(Request $request, $uid)
// {
//     // Find the user by user ID
//     $user = User::findOrFail($uid);

//     // Validate the request data
//     $validatedData = $request->validate([
//         'cardNumber' => 'required|string|max:19',
//         'cardName' => 'required|string|max:255',
//         'points' => 'required|integer',
//         'expireDate' => 'required|date_format:m/y',
//         'cvv' => 'required|string|max:4',
//     ]);

//     // Calculate the total points including the current user points
//     $totalPoints = $user->points + $validatedData['points'];

//     // Retrieve the latest payment for the user, if any
//     $latestPayment = Payment::where('uid', $user->id)->get()->last();

//     if ($latestPayment) {
//         // If there is a previous payment, update points accordingly
//         $validatedData['points'] += $latestPayment->points;
//     }

//     $payment = Payment::create([
//         'uid' => $user->id,
//         'cardNumber' => $validatedData['cardNumber'],
//         'cardName' => $validatedData['cardName'],
//         'points' => $totalPoints,
//         'expireDate' => $validatedData['expireDate'],
//         'cvv' => $validatedData['cvv'],
//     ]);

//     // Update user's points after storing payment
//     $user->points = $totalPoints;
//     $user->save();

//     return response()->json(['message' => 'Payment stored successfully and points updated', 'payment' => $payment], 201);
// }

public function payment(Request $request, $uid)
{
    // Find the user by user ID
    $user = User::findOrFail($uid);

    // Validate the request data
    $validatedData = $request->validate([
        'cardNumber' => 'required|string|max:19',
        'cardName' => 'required|string|max:255',
        'points' => 'required|integer',
        'expireDate' => 'required|date_format:m/y',
        'cvv' => 'required|string|max:4',
    ]);

    // Define an array of valid card numbers and their corresponding CVVs
    $validCards = [
        '1234-5678-9012-3456' => '123',
        '9876-5432-1098-7654' => '456',
        '1111-2222-3333-4444' => '789',
        '5555-6666-7777-8888' => '012',
        '9999-0000-1111-2222' => '345',
        '3333-4444-5555-6666' => '678',
        // Add more valid card numbers and CVVs as needed
    ];

    // Check if the card number and CVV match any of the valid combinations
    if (!isset($validCards[$validatedData['cardNumber']]) || $validCards[$validatedData['cardNumber']] !== $validatedData['cvv']) {
        return response()->json(['message' => 'Invalid card number or CVV'], 400);
    }

    // Calculate the total points including the current user points
    $totalPoints = $user->points + $validatedData['points'];

    // Retrieve the latest payment for the user, if any
    $latestPayment = Payment::where('uid', $user->id)->get()->last();

    if ($latestPayment) {
        // If there is a previous payment, update points accordingly
        $validatedData['points'] += $latestPayment->points;
    }

    $payment = Payment::create([
        'uid' => $user->id,
        'cardNumber' => $validatedData['cardNumber'],
        'cardName' => $validatedData['cardName'],
        'points' => $totalPoints,
        'expireDate' => $validatedData['expireDate'],
        'cvv' => $validatedData['cvv'],
    ]);

    // Update user's points after storing payment
    $user->points = $totalPoints;
    $user->save();

    return response()->json(['message' => 'Payment stored successfully and points updated', 'payment' => $payment], 201);
}


}
