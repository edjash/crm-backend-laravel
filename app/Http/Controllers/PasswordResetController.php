<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\PasswordReset;
use DateTime;
use App\Mail\PasswordResetCode;
use Exception;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    public function index(Request $request)
    {
        $this->deleteStaleTokens();

        switch ($request->input('step')) {
            case 1:
            default:
                return $this->stepOne($request);
                break;
            case 2:
                return $this->stepTwo($request);
                break;
            case 3:
                return $this->stepThree($request);
                break;
        }
    }

    private function stepOne(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email'
        ]);

        $response = [];

        $user = User::where('email', $data['email'])->first();


        [$code, $codeHash] = $this->genCode(6);
        [$token, $tokenHash] = $this->genToken();

        if ($user) {
            $pr = PasswordReset::where('email', $data['email'])->first();
            if ($pr) {
                $pr->delete();
            }

            $expiryDate = new DateTime('now');
            $expiryDate->modify('+10 minutes');

            $pr = new PasswordReset;
            $pr->user_id = $user->id;
            $pr->email = $data['email'];
            $pr->step = 2;
            $pr->code = $codeHash;
            $pr->token = $tokenHash;
            $pr->ip_address = $request->ip();
            $pr->user_agent = $request->userAgent();
            $pr->tries = 0;
            $pr->expires_at = $expiryDate->format('Y-m-d H:i:s');
            $pr->save();
            $response['code'] = $code;

            Mail::to($user)->send(new PasswordResetCode($user, $code));
        }

        $response['fieldValues'] = ['token' => $token];

        return response()->json($response);
    }

    private function stepTwo(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'code' => 'required|string',
        ]);

        $error = false;
        try {
            $this->validateAttempt($request, 2, $data['email'], $data['code'], $data['token']);
        } catch (Exception $e) {
            $error = $e;
        }

        $pr = PasswordReset::where('email', $data['email'])->where('step', 2)->first();
        if ($pr) {
            if ($error) {
                $pr->tries++;
                $pr->save();
            } else {
                $pr->step = 3;
                $pr->save();
            }
        }

        if ($error) {
            $this->logError($request, $error->getMessage());
            return $this->jsonError('The code was invalid or has expired.', 422);
        }

        return response()->json("OK");
    }

    private function stepThree(Request $request)
    {
        $data = $request->validate([
            'password' => 'required|string|min:5',
            'email' => 'required|email',
            'token' => 'required|string',
            'code' => 'required|string',
        ]);

        $error = false;
        try {
            $this->validateAttempt($request, 3, $data['email'], $data['code'], $data['token']);
        } catch (Exception $error) {
            $error = $error;
        }

        $pr = PasswordReset::where('email', $data['email'])->where('step', 3)->first();
        if ($pr && !$error) {
            $user = User::where('id', $pr->user_id)->first();
            $pr->delete();
            $user->password = Hash::make($data['password']);
            $user->save();
            return response()->json("OK");
        }

        $this->logError($request, $error->getMessage());
        return $this->jsonError('The password reset time limit has expired.', 422);
    }

    private function validateAttempt(Request $request, int $step, string $email, string $code, string $token)
    {
        $pr = PasswordReset::where('email', $email)->first();
        if (!$pr) {
            throw new Exception('email address not found');
        }
        if ($pr->step != $step) {
            throw new Exception('step does not match');
        }
        if ($pr->ip_address != $request->ip()) {
            throw new Exception('ip does not match');
        }
        if ($pr->user_agent != $request->userAgent()) {
            throw new Exception('user agent does not match');
        }
        if (!Hash::check($code, $pr->code)) {
            throw new Exception('code does not match');
        }
        if (!Hash::check($token, $pr->token)) {
            throw new Exception('token does not match');
        }
        $user = User::where('id', $pr->user_id)->first();
        if (!$user) {
            throw new Exception('user not found');
        }
        if ($user->email !== $email) {
            throw new Exception('submitted email does not match a user');
        }
    }

    public function deleteStaleTokens()
    {
        $date = new DateTime;
        PasswordReset::where('expires_at', '<=', $date->format('Y-m-d H:i:s'))->delete();
    }

    private function genCode($length)
    {
        $code = strtoupper(Str::Random(6));
        $hash = Hash::make($code);

        $validator = Validator::make(['code' => $hash], ['code' => 'unique:password_resets,code']);

        if ($validator->fails()) {
            return $this->genCode($length);
        }

        return [$code, $hash];
    }

    private function genToken()
    {
        $token = Str::uuid();
        $hash = Hash::make($token);

        $validator = Validator::make(['token' => $hash], ['token' => 'unique:password_resets,token']);

        if ($validator->fails()) {
            return $this->genToken();
        }

        return [$token, $hash];
    }

    private function logError(Request $request, string $message)
    {
        $data = array_merge($request->input(), ["ip" => $request->ip(), "ua" => $request->userAgent()]);
        Log::channel('password_reset')->error($message, $data);
    }
}
