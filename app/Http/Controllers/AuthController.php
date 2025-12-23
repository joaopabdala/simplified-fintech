<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * @group Populating
 *
 * Utility endpoints for user registration and testing.
 * Note: These routes are public and used solely to populate the system with test users.
 */
class AuthController extends Controller
{
    /**
     * POST /api/register
     *
     * Create a user and a wallet.
     * @bodyParam first_name string required Example: John
     * @bodyParam last_name string required Example: Doe
     * @bodyParam email string required Example: john@example.com
     * @bodyParam document string required Example: 12345678901
     * @bodyParam user_type string required common|shop. Example: common
     * @bodyParam password string required Example: password
     */
    public function register(RegisterRequest $request)
    {
        try {
            $data = DB::transaction(function () use ($request) {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'document' => $request->document,
                    'user_type' => $request->user_type,
                    'password' => Hash::make($request->password),
                ]);

                Wallet::create([
                    'user_id' => $user->id,
                ]);

                return [
                    'user' => $user,
                    'token' => $user->createToken('token')->plainTextToken,
                ];
            });

            return response()->json($data, 201);
        } catch (\Exception $e) {
            Log::error('Register error: '.$e->getMessage());

            return response()->json(['error' => 'Failed to register.'], 500);
        }
    }

    /**
     * POST /api/login
     *
     * Get a bearer token.
     * @bodyParam email string required Example: john@example.com
     * @bodyParam password string required Example: password
     */
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('token')->plainTextToken,
        ]);
    }
}
