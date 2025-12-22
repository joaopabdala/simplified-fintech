<?php

namespace Tests\Feature;

use App\Http\Enums\TransferTypeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransferTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_transfer_between_users(): void
    {
        $payer = User::factory()->common()->create();
        $payee = User::factory()->common()->create();

        $amountInWallet = 100.00;
        $payer->wallet->increment('balance', $amountInWallet);
        $amountPayment = 50.00;
        $response = $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => $amountPayment,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transfers', [
                'payee_wallet_id' => $payee->wallet->id,
                'payer_wallet_id' => $payer->wallet->id,
                'amount'          => $amountPayment,
                'transfer_type'   => TransferTypeEnum::USER_PAYMENT,
            ]
        );

        $this->assertEquals(50.00, $payer->wallet->refresh()->balance);
        $this->assertEquals(50.00, $payee->wallet->refresh()->balance);
    }

    public function test_transfer_to_shop(): void
    {
        $payer = User::factory()->common()->create();
        $payee = User::factory()->shop()->create();

        $amountInWallet = 100.00;
        $payer->wallet->increment('balance', $amountInWallet);
        $amountPayment = 50.00;
        $response = $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => $amountPayment,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transfers', [
                'payee_wallet_id' => $payee->wallet->id,
                'payer_wallet_id' => $payer->wallet->id,
                'amount'          => $amountPayment,
                'transfer_type'   => TransferTypeEnum::SHOP_PAYMENT,
            ]
        );

        $this->assertEquals(50.00, $payer->wallet->refresh()->balance);
        $this->assertEquals(50.00, $payee->wallet->refresh()->balance);
    }

    public function test_shop_cant_transfer(): void
    {
        $payer = User::factory()->shop()->create();
        $payee = User::factory()->common()->create();

        $amountInWallet = 100.00;
        $payer->wallet->increment('balance', $amountInWallet);
        $amountPayment = 50.00;
        $response = $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => $amountPayment,
        ]);

        $response->assertStatus(422);
    }

    public function test_cant_transfer_insufficient_value(): void
    {
        $payer = User::factory()->common()->create();
        $payee = User::factory()->shop()->create();

        $amountPayment = 50.00;
        $response = $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => $amountPayment,
        ]);

        $response->assertStatus(422);
    }
}
