<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        try {
            $validatedData = $validate->validated();
            $validatedData['password'] = Hash::make($validatedData['password']);
            User::create($validatedData);

            return response()->json([
                "message" => "User successfully register!",
                "success" => true
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                "error" => $e->getMessage(),
                "success" => false
            ], 500);
        }


    }
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = auth()->guard('api')->attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid Credentials',
                    'success' => false,
                ], 401);
            }

            return response()->json([
                'success' => true,
                'user' => auth()->guard('api')->user(),
                'token' => $token
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function logout()
    {
        $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

        if ($removeToken) {
            return response()->json([
                'success' => true,
                'message' => 'Logout Success!',
            ], 200);
        }
    }
}
