<?php

namespace Tests\Feature;

use App\Http\Enums\TransferTypeEnum;
use App\Models\User;
use Tests\TestCase;

class WalletTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_deposit_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallet;
        $depositAmount = 100;
        $response = $this->postJson(route('wallet.deposit', ['wallet' => $wallet->id]), [
            'amount' => $depositAmount,
        ]);

        $response->assertStatus(200);

        $this->assertEquals($depositAmount, data_get($response, 'data.balance'));
        $this->assertEquals($depositAmount, $wallet->refresh()->balance);

        $this->assertDatabaseHas('transfers', [
            'payee_wallet_id' => $wallet->id,
            'payer_wallet_id' => null,
            'amount' => $depositAmount,
            'transfer_type' => TransferTypeEnum::DEPOSIT,
        ]);
    }
}
