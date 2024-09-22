<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(AuthRegisterRequest $request)
    {
        try {
            $data = $request->validated();
            $user = User::create($data);
            $access_token = $user->createToken($user, ['*'], now()->addMinutes(30));
            return response()->json(['access_token' => $access_token->plainTextToken]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function login(AuthLoginRequest $request)
    {
        try {
            $data = $request->validated();
            $user = User::where('email', $data['email'])->first();
            if (!$user || !Hash::check($data['password'], $user->password)) {
                return response()->json(['error' => 'User cridentails are not currect'], 401);
            }
            $user->tokens()->delete();
            $access_token = $user->createToken($user, ['*'], now()->addMinutes(30));
            return response()->json(['access_token' => $access_token->plainTextToken]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logout successfully']);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 400);
        }
    }
}
