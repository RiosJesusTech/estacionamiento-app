<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'auth_code' => 'required|string|in:1234a,admin123,gestor456',
            'placas' => 'nullable|string|max:255'
        ]);

        $role = 'user'; // default
        if ($request->auth_code === 'admin123') {
            $role = 'admin';
        } elseif ($request->auth_code === 'gestor456') {
            $role = 'gestor';
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role
        ]);

        // If placas provided, create VehiculoPerfil
        if ($request->placas) {
            \App\Models\VehiculoPerfil::create([
                'placas' => $request->placas,
                'nombre_cliente' => $request->name,
                'telefono' => $request->telefono ?? '',
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
                'errors' => [
                    'username' => ['Usuario o contraseña incorrectos']
                ]
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function checkAuth(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'authenticated' => true,
            'user' => $user->only(['id', 'name', 'username', 'role'])
        ]);
    }

    public function loginByPlate(Request $request)
    {
        $request->validate([
            'placas' => 'required|string',
            'telefono' => 'required|string'
        ]);

        $vehicleProfile = \App\Models\VehiculoPerfil::where('placas', $request->placas)
            ->where('telefono', $request->telefono)
            ->first();

        if (!$vehicleProfile || !$vehicleProfile->user_id) {
            return response()->json([
                'message' => 'Vehículo no encontrado o no registrado',
                'errors' => [
                    'placas' => ['Placas o teléfono incorrectos']
                ]
            ], 401);
        }

        $user = \App\Models\User::find($vehicleProfile->user_id);
        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'errors' => [
                    'placas' => ['Usuario no encontrado']
                ]
            ], 401);
        }

        \Illuminate\Support\Facades\Auth::login($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role
            ]
        ]);
    }
}
