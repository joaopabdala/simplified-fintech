<?php

namespace App\Actions\Transfer;

use App\Exceptions\TransferException;
use App\Http\Enums\TransferTypeEnum;
use App\Http\Enums\UserTypeEnum;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Services\Authorization\AuthorizationInterface;
use App\Services\Authorization\AuthorizationProvider;
use Illuminate\Support\Facades\DB;

class HandleTransferAction
{

    public function __construct(protected AuthorizationInterface $authorizer)
    {
    }

    public function execute(User $payer, User $payee, float $value): Transfer
    {
        if ($payer->user_type == UserTypeEnum::SHOP) {
            throw TransferException::ShopTypeUsersCantTransfer();
        }

        if(!$this->authorizer->isAuthorized()){
            throw TransferException::NotAuthorized();
        }

        return DB::transaction(function () use ($payer, $payee, $value) {

            $payerWallet = Wallet::where('user_id', $payer->id)
                ->lockForUpdate()
                ->firstOrFail();

            $payeeWallet = Wallet::where('user_id', $payee->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payerWallet->balance < $value) {
                throw TransferException::InsufficientBalance();
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
