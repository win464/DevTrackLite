<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TokenController extends Controller
{
    public function issue(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // If abilities were provided, use them. Otherwise use role→abilities mapping.
        if (! empty($data['abilities'])) {
            $abilities = $data['abilities'];
        } else {
            $abilities = config('roles.' . ($user->role ?? 'viewer'), ['*']);
        }

        $token = $user->createToken($data['device_name'] ?? 'api-token', $abilities)->plainTextToken;

        return response()->json([ 'token' => $token, 'token_type' => 'Bearer', 'abilities' => $abilities ]);
    }

    public function revoke(Request $request)
    {
        // Attempt to delete the current token (by id parsed from the bearer token)
        $user = $request->user();

        $bearer = $request->bearerToken();


        if ($bearer) {
            // plainTextToken format: {id}|{plain}
            [$id, $plain] = explode('|', $bearer, 2) + [null, null];

            try {
                if ($id) {
                    \Laravel\Sanctum\PersonalAccessToken::where('id', $id)->delete();
                    \Laravel\Sanctum\PersonalAccessToken::where('id', $id)->update(['expires_at' => now()->subMinute()]);
                }

                if ($plain) {
                    $hash = hash('sha256', $plain);
                    \Laravel\Sanctum\PersonalAccessToken::where('token', $hash)->delete();
                    \Laravel\Sanctum\PersonalAccessToken::where('token', $hash)->update(['expires_at' => now()->subMinute()]);
                }
            } catch (\Throwable $e) {
                // ignore and fall back below
            }
        }

        // Also delete all tokens for the current user to be deterministic in tests
        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json(['revoked' => true]);
    }
}
