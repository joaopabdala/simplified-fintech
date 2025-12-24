<?php

namespace App\Actions\Wallet;

use App\Http\Enums\TransferTypeEnum;
use App\Models\Transfer;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class HandleWalletDepositAction
{
    public function execute(Wallet $wallet, float $amount): Wallet
    {
        return DB::transaction(function () use ($wallet, $amount) {

            $wallet->increment('balance', $amount);

            Transfer::create([
                'payee_wallet_id' => $wallet->id,
                'amount' => $amount,
                'transfer_type' => TransferTypeEnum::DEPOSIT,
            ]);

            return $wallet->refresh();
        });
    }
}
