<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use App\Mail\Registered;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5',
        ]);

        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        Mail::to($user)->send(new Registered($user));

        return response()->json([
            "user" => $user,
        ]);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:5',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            $errors = ['The login details were not recognised.'];

            return response()->json([
                'errors' => ['auth' => $errors]
            ], 401);
        }

        $request->session()->regenerate();

        $user = User::where('email', $validatedData['email'])->firstOrFail();

        return response()->json([
            "user" => $user,
        ]);
    }
}
