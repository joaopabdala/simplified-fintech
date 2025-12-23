<?php

namespace App\Http\Controllers;

use App\Http\Enums\TransferTypeEnum;
use App\Http\Requests\WalletDepositRequest;
use App\Http\Resources\WalletResource;
use App\Models\Transfer;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function response;

/**
 * @group Wallets
 *
 * APIs for managing user wallets, checking balances, and processing deposits.
 */
class WalletController extends Controller
{
    /**
     * GET /api/wallet
     * * Get a paginated list of all registered wallets.
     */
    public function index()
    {
        $wallets = Wallet::orderByDesc('created_at')->paginate(5);

        return WalletResource::collection($wallets);
    }

    /**
     * GET /api/wallet/{id}
     * * Retrieve details and current balance of a specific wallet.
     */
    public function show(Wallet $wallet)
    {
        return WalletResource::make($wallet);
    }

    /**
     * POST /api/wallet/{id}/deposit
     * * Add money to a specific wallet. This operation creates a transfer record
     * of type "DEPOSIT" and updates the wallet balance atomically.
     * * @urlParam wallet int required The ID of the wallet. Example: 1
     * * @bodyParam amount float required The amount to deposit. Must be positive. Example: 500.00
     * * @response 200 {
     * "data": {
     * "id": 1,
     * "user_id": 1,
     * "balance": 500.00,
     * "user_type": "common"
     * }
     * }
     * @response 500 {
     * "error": "Internal Server Error. Please try again later"
     * }
     */
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
            Log::error('Deposit error: '.$e->getMessage());

            return response()->json([
                'error' => 'Internal Server Error. Please try again later',
            ], 500);
        }

        return WalletResource::make($wallet->refresh());
    }
}
