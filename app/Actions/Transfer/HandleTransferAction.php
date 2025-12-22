<?php

namespace App\Actions\Transfer;

use App\Http\Enums\TransferTypeEnum;
use App\Http\Enums\UserTypeEnum;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class HandleTransferAction
{

    public function execute(User $payer, User $payee, float $value): Transfer
    {
        if ($payer->user_type == UserTypeEnum::SHOP) {
            throw new \InvalidArgumentException("Shop type users can't do transfers");
        }

        return DB::transaction(function () use ($payer, $payee, $value) {

            $payerWallet = Wallet::where('user_id', $payer->id)
                ->lockForUpdate()
                ->firstOrFail();

            $payeeWallet = Wallet::where('user_id', $payee->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payerWallet->balance < $value) {
                throw new \InvalidArgumentException('Insufficient balance.');
            }

            $payerWallet->decrement('balance', $value);
            $payeeWallet->increment('balance', $value);

            $transferType = $payee->user_type == UserTypeEnum::COMMON
                ? TransferTypeEnum::USER_PAYMENT
                : TransferTypeEnum::SHOP_PAYMENT;

            return Transfer::create([
                'payer_wallet_id' => $payerWallet->id,
                'payee_wallet_id' => $payeeWallet->id,
                'amount' => $value,
                'transfer_type' => $transferType,
            ]);
        });
    }
}
