<?php

namespace App\Http\Controllers;

use App\Http\Resources\WalletResource;
use App\Models\Wallet;

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
}
