<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;
use Exception;
use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends Controller
{
    public function verifyEmail(string $id)
    {
        $user = User::findOrFail($id);

        try {
            $user->email_verified_at = now();
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function resendEmail(Request $request)
    {
        $email = Validator::make($request->only('email'), [
            'email' => 'required|email',
        ]);

        if ($email->fails()) {
            return response()->json([
                'success' => false,
                'message' => $email->errors(),
            ], 422);
        }

        $validatedEmail = $email->validated();
        $user = User::whereEmail($validatedEmail['email'])->firstOrFail();

        $urlVerify = url('/user/verify-email/' . $user->id);
        try {
            Mail::to($validatedEmail['email'])->send(new EmailVerification($urlVerify));
            return response()->json([
                'success' => true,
                'message' => 'Successfully resend email verification',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
