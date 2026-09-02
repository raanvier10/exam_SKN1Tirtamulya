<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password, 'role' => 'siswa', 'status' => 'active'])) {
            $user = Auth::user();
            
            // Generate simple token for MVP
            $token = Str::random(60);
            $user->api_token = hash('sha256', $token);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username atau password salah',
        ], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->api_token = null;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    public function updateGoogleStatus(Request $request)
    {
        $request->validate([
            'connected' => 'required|boolean',
            'email' => 'nullable|string',
        ]);

        $user = $request->user();
        $user->google_connected = $request->connected;
        if ($request->has('email')) {
            $user->google_email = $request->email;
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Status akun Google berhasil diperbarui',
            'data' => $user
        ]);
    }
}
