<?php

namespace Tests\Unit;

use App\Actions\Transfer\HandleTransferAction;
use App\Events\TransferCompletedEvent;
use App\Exceptions\TransferException;
use App\Models\User;
use App\Services\Authorization\AuthorizationInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class HandleTransferTest extends TestCase
{
    use RefreshDatabase;

    protected $authorizer;

    protected $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizer = $this->createMock(AuthorizationInterface::class);
        $this->action = new HandleTransferAction($this->authorizer);

        Event::fake();
    }

    public function test_should_transfer_money_successfully(): void
    {
        $payer = User::factory()->common()->create();
        $payee = User::factory()->common()->create();

        $payer->wallet()->update(['balance' => 100.00]);

        $this->authorizer->method('isAuthorized')->willReturn(true);

        $transfer = $this->action->execute($payer, $payee, 50.00);

        $this->assertEquals(50.00, $transfer->amount);
        $this->assertEquals(50.00, $payer->wallet->refresh()->balance);
        $this->assertEquals(50.00, $payee->wallet->refresh()->balance);

        Event::assertDispatched(TransferCompletedEvent::class);
    }

    public function test_shop_users_cannot_transfer_money(): void
    {
        $payer = User::factory()->shop()->create();
        $payee = User::factory()->create();

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage(TransferException::ShopTypeUsersCantTransfer()->getMessage());

        $this->action->execute($payer, $payee, 10.00);
    }

    public function test_should_throw_exception_if_balance_is_insufficient(): void
    {
        $payer = User::factory()->common()->create();
        $payer->wallet()->update(['balance' => 5.00]);
        $payee = User::factory()->create();

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage(TransferException::InsufficientBalance()->getMessage());

        $this->action->execute($payer, $payee, 100.00);
    }

    public function test_should_not_transfer_if_not_authorized_externally(): void
    {
        $payer = User::factory()->common()->create();
        $payer->wallet()->update(['balance' => 100.00]);
        $payee = User::factory()->create();

        $this->authorizer->method('isAuthorized')->willReturn(false);

        $this->expectException(TransferException::class);

        $this->action->execute($payer, $payee, 50.00);

        $this->assertEquals(100.00, $payer->wallet->refresh()->balance);
        $this->assertEquals(0, $payee->wallet->refresh()->balance);
    }
}
