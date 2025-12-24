<?php

namespace Tests\Feature;

use App\Exceptions\TransferException;
use App\Http\Enums\TransferTypeEnum;
use App\Jobs\TransferNotificationJob;
use App\Models\User;
use App\Services\Authorization\AuthorizationInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function mockAuthorization(bool $authorized = true): void
    {
        $this->mock(AuthorizationInterface::class, function ($mock) use ($authorized) {
            $mock->shouldReceive('isAuthorized')
                ->once()
                ->andReturn($authorized);
        });
    }

    public function test_transfer_between_users(): void
    {
        $this->mockAuthorization();

        $payer = User::factory()->common()->create();
        $payee = User::factory()->common()->create();

        $amountInWallet = 100.00;
        $payer->wallet->update(['balance' => $amountInWallet]);
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
            'amount' => $amountPayment,
            'transfer_type' => TransferTypeEnum::USER_PAYMENT,
        ]
        );

        $this->assertEquals(50.00, $payer->wallet->refresh()->balance);
        $this->assertEquals(50.00, $payee->wallet->refresh()->balance);

        $this->assertDatabaseCount('notifications', 2);

        Queue::assertPushed(TransferNotificationJob::class, 2);
    }

    public function test_transfer_to_shop(): void
    {
        $this->mockAuthorization();

        $payer = User::factory()->common()->create();
        $payee = User::factory()->shop()->create();

        $amountInWallet = 100.00;
        $payer->wallet->update(['balance' => $amountInWallet]);
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
            'amount' => $amountPayment,
            'transfer_type' => TransferTypeEnum::SHOP_PAYMENT,
        ]
        );

        $this->assertEquals(50.00, $payer->wallet->refresh()->balance);
        $this->assertEquals(50.00, $payee->wallet->refresh()->balance);

        $this->assertDatabaseCount('notifications', 2);
        Queue::assertPushed(TransferNotificationJob::class, 2);
    }

    public function test_shop_cant_transfer(): void
    {

        $payer = User::factory()->shop()->create();
        $payee = User::factory()->common()->create();

        $amountInWallet = 100.00;
        $payer->wallet->update(['balance' => $amountInWallet]);
        $amountPayment = 50.00;
        $response = $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => $amountPayment,
        ]);

        $response->assertStatus(TransferException::ShopTypeUsersCantTransfer()->getCode());

        $response->assertJson([
            'error' => TransferException::ShopTypeUsersCantTransfer()->getMessage(),
        ]);

        $this->assertDatabaseCount('notifications', 0);
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

        $response->assertStatus(TransferException::InsufficientBalance()->getCode());

        $response->assertJson([
            'error' => TransferException::InsufficientBalance()->getMessage(),
        ]);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_cannot_transfer_unauthorized(): void
    {
        $this->mockAuthorization(false);

        $payer = User::factory()->common()->create();
        $payee = User::factory()->common()->create();

        $payer->wallet->update(['balance' => 100.00]);
        $amountPayment = 50.00;

        $response = $this->postJson('/api/transfer', [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => $amountPayment,
        ]);

        $response->assertStatus(TransferException::NotAuthorized()->getCode());

        $response->assertJson([
            'error' => TransferException::NotAuthorized()->getMessage(),
        ]);

        $this->assertEquals(100.00, $payer->wallet->refresh()->balance);
        $this->assertEquals(0.00, $payee->wallet->refresh()->balance);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_idempotency_middleware(): void
    {

        $this->mock(AuthorizationInterface::class, function ($mock) {
            $mock->shouldReceive('isAuthorized')
                ->once()
                ->andReturn(true);
        });

        $payer = User::factory()->common()->create();
        $payee = User::factory()->common()->create();
        $idempotencyKey = 'test-key-123';

        $payer->wallet()->update(['balance' => 1000.00]);

        $payload = [
            'payer' => $payer->id,
            'payee' => $payee->id,
            'value' => 100.00,
        ];

        $response1 = $this->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson('/api/transfer', $payload);

        $response1->assertStatus(201);

        $this->assertEquals(900, $payer->wallet->refresh()->balance);

        $response2 = $this->withHeader('X-Idempotency-Key', $idempotencyKey)
            ->postJson('/api/transfer', $payload);

        $response2->assertStatus(200);

        $this->assertEquals(900, $payer->wallet->refresh()->balance);
        $this->assertEquals($response1->json(), $response2->json());

        Queue::assertPushed(TransferNotificationJob::class, 2);
    }
}
