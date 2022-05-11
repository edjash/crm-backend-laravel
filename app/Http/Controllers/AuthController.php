<?php

namespace App\Http\Controllers;

use App\Mail\Registered;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

        return $this->sendAppInit($user);
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
                'errors' => ['auth' => $errors],
            ], 401);
        }

        $request->session()->regenerate();

        $user = User::where('email', $validatedData['email'])->firstOrFail();

        return $this->sendAppInit($user);
    }

    public function sendAppInit($user)
    {
        $serverCfg = Config::get('crm');

        return response()->json([
            "userInfo" => $user,
            "serverCfg" => $serverCfg,
        ]);
    }
}
