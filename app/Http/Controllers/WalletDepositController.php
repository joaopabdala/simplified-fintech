<?php

namespace App\Http\Controllers;

use App\Actions\Wallet\HandleWalletDepositAction;
use App\Http\Requests\WalletDepositRequest;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\Log;

use function response;

class WalletDepositController extends Controller
{
    /**
     * POST /api/wallet/{id}/deposit
     * * Add money to a specific wallet. This operation creates a transfer record
     * of type "DEPOSIT" and updates the wallet balance atomically.
     *
     * * @urlParam wallet int required The ID of the wallet. Example: 1
     *
     * * @bodyParam amount float required The amount to deposit. Must be positive. Example: 500.00
     *
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
    public function __invoke(WalletDepositRequest $request, Wallet $wallet, HandleWalletDepositAction $handleWalletDepositAction)
    {
        $amount = $request->amount;

        try {
            $wallet = $handleWalletDepositAction->execute($wallet, $amount);
        } catch (Exception $e) {
            Log::error('Deposit error: '.$e->getMessage());

            return response()->json([
                'error' => 'Internal Server Error. Please try again later',
            ], 500);
        }

        return WalletResource::make($wallet->refresh());
    }
}
