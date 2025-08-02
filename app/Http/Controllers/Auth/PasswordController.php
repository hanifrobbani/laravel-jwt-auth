<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
class PasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'message' => $status === Password::RESET_LINK_SENT
                ? 'Reset password link sent to your email.'
                : 'Unable to send reset link.'
        ], $status === Password::RESET_LINK_SENT ? 200 : 400);
    }

    public function setNewPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        return response()->json([
            'message' => $status === Password::PASSWORD_RESET
                ? 'Password has been reset successfully.'
                : 'Invalid token or email.',
        ], $status === Password::PASSWORD_RESET ? 200 : 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'new_password' => 'required|max:255',
        ]);

        $user = Auth::user();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password did not match!',
            ], 401);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password successfully updated',
            'success' => true,
        ]);
    }

}
