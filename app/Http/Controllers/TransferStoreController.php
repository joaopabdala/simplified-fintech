<?php

namespace App\Http\Controllers;

use App\Actions\Transfer\HandleTransferAction;
use App\Exceptions\TransferException;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransferResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

use function response;

/**
 * @group Transfers
 */
class TransferStoreController extends Controller
{
    /**
     * POST /api/transfer
     *
     * This endpoint triggers a peer-to-peer (P2P) or P2B transfer.
     * It validates balance, checks for external authorization, and processes
     * the transaction atomically to ensure data integrity.
     *
     * @header X-Idempotency-Key string A unique UUID to prevent duplicate transactions. Example: 550e8400-e29b-41d4-a716-446655440000
     * @bodyParam payer int required The ID of the user sending the funds. Example: 1
     * @bodyParam payee int required The ID of the user/merchant receiving the funds. Example: 5
     * @bodyParam value float required The amount to be transferred. Must be greater than 0. Example: 100.50
     *
     * * @response scenario=success {
     * "data": {
     * "id": 1024,
     * "payer_wallet_id": 1,
     * "payee_wallet_id": 5,
     * "amount": 100.50,
     * "transfer_type": "USER_PAYMENT",
     * "created_at": "2025-12-23T11:00:00Z"
     * }
     * }
     * * @response 422 scenario="Insufficient Balance" {
     * "message": "The payer does not have enough balance to complete this transaction."
     * }
     * * @response 403 scenario="Invalid User Type" {
     * "message": "Merchant users are not allowed to initiate transfers."
     * }
     * * @response 401 scenario="Unauthorized" {
     * "message": "The external authorization service denied this transaction."
     * }
     *  * @response 500 scenario="Internal Server Error" {
     *  "message": "Internal Server Error. Please try again later."
     *  }
     */
    public function __invoke(TransferRequest $request, HandleTransferAction $handleTransferAction)
    {
        $payer = User::findOrFail($request->payer);
        $payee = User::findOrFail($request->payee);
        $value = $request->value;

        try {
            $transfer = $handleTransferAction->execute($payer, $payee, $value);
        } catch (TransferException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Transfer store error: '.$e->getMessage());

            return response()->json([
                'error' => 'Internal Server Error. Please try again later.',
            ], 500);
        }

        return TransferResource::make($transfer);
    }
}
