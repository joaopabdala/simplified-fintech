<?php

namespace App\Http\Controllers;

use App\Http\Enums\TransferTypeEnum;
use App\Http\Requests\WalletDepositRequest;
use App\Http\Resources\WalletResource;
use App\Models\Transfer;
use App\Models\Wallet;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function response;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $wallets = Wallet::orderByDesc('created_at')->paginate(5);

        return WalletResource::collection($wallets);
    }

    /**
     * Display the specified resource.
     */
    public function show(Wallet $wallet)
    {
        return WalletResource::make($wallet);
    }

    public function deposit(WalletDepositRequest $request, Wallet $wallet)
    {
        $amount = $request->amount;

        try {
            DB::transaction(function () use ($wallet, $amount) {

                $wallet->increment('balance', $amount);

                Transfer::create([
                    'payee_wallet_id' => $wallet->id,
                    'amount' => $amount,
                    'transfer_type' => TransferTypeEnum::DEPOSIT,
                ]);
            });
        } catch (Exception $e) {
            Log::error("Deposit error: " . $e->getMessage());
            return response()->json([
                'error' => 'Internal Server Error. Please try again later'
            ], 500);
        }
        return WalletResource::make($wallet->refresh());
    }

}
