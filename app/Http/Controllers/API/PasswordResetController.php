<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    // 1. Send OTP to Email
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $otp = rand(1000, 9999); // 4-digit OTP
        $expiresAt = Carbon::now()->addMinutes(10); // OTP valid for 10 minutes

        // Store OTP in 'otps' table
        DB::table('otps')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp' => $otp,
                'expires_at' => $expiresAt,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ]
        );

        try {
            // Send Email
            Mail::raw("Your OTP is: $otp", function($message) use ($request) {
                $message->to($request->email)
                        ->subject('Password Reset OTP');
            });

            return response()->json(['success' => true, 'message' => 'OTP sent to your email']);
        } catch (\Exception $e) {
            // If Mail fails
            return response()->json(['success' => false, 'message' => 'Failed to send OTP: '.$e->getMessage()], 500);
        }
    }

    // 2. Verify OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);

        $record = DB::table('otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP'], 400);
        }

        if (Carbon::parse($record->expires_at)->isPast()) {
            return response()->json(['success' => false, 'message' => 'OTP expired'], 400);
        }

        return response()->json(['success' => true, 'message' => 'OTP verified successfully']);
    }

    // 3. Reset Password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed'
        ]);

        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete OTP after success
        DB::table('otps')->where('email', $request->email)->delete();

        return response()->json(['success' => true, 'message' => 'Password updated successfully']);
    }
}
