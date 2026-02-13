<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
     public function login(Request $request)
    {
        $request->validate(['email' => 'required', 'password' => 'required']);
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credential tidak valid'], 401);
        }

        $token = $user->createToken('BreadDesk')->plainTextToken;
        $response['success'] = true;
        $response['message'] = 'Login berhasil';
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user,
        ]);
    }


    public function logout(Request $request)
    {

        // Ambil data user sebelum logout
        $user = $request->user();

        // Hapus token saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
            'email' => $user->email,
            'name' => $user->name
        ], Response::HTTP_OK);
    }
}
