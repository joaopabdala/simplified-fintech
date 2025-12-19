<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email'     => $request->email,
                'document'  => $request->document,
                'user_type'  => $request->user_type,
                'password'  => Hash::make($request->password),
            ]);

            Wallet::create([
                'user_id' => $user->id,
            ]);
        DB::commit();
        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('token')->plainTextToken
        ]);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'user'  => $user,
            'token' => $user->createToken('token')->plainTextToken
        ]);
    }
}
