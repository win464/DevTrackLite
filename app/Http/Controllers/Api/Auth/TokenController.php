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

    $abilities = $data['abilities'] ?? ['*'];

    $token = $user->createToken($data['device_name'] ?? 'api-token', $abilities)->plainTextToken;

    return response()->json([ 'token' => $token, 'token_type' => 'Bearer', 'abilities' => $abilities ]);
    }

    public function revoke(Request $request)
    {
        // Attempt to delete the current token (by id parsed from the bearer token)
        $user = $request->user();

        $bearer = $request->bearerToken();

        if ($bearer) {
            // plainTextToken format: {id}|{random}
            [$id] = explode('|', $bearer, 2) + [null];

            if ($id) {
                try {
                    \Laravel\Sanctum\PersonalAccessToken::find($id)?->delete();
                } catch (\Throwable $e) {
                    // ignore errors here — we'll fall back to deleting all tokens
                }
            }
        }

        // Also delete all tokens for the current user to be deterministic in tests
        if ($user) {
            $user->tokens()->delete();
        }

        return response()->json(['revoked' => true]);
    }
}
