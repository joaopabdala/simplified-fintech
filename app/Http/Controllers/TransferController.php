<?php

namespace App\Http\Controllers;

use App\Http\Resources\TransferResource;
use App\Models\Transfer;

/**
 * @group Transfers
 *
 * APIs for viewing the history and details of transfers performed within the system.
 */
class TransferController extends Controller
{
    /**
     * GET /api/transfers
     *
     * Retrieve a paginated list of all transfers (deposits included), sorted by the most recent.
     *
     * @queryParam page int The page number for pagination. Example: 1
     */
    public function index()
    {
        $transfers = Transfer::orderByDesc('created_at')->paginate(5);

        return TransferResource::collection($transfers);
    }

    /**
     * GET /api/transfer/{id}
     *
     * Retrieve full details of a specific transaction, including payer, payee, and transfer type.
     *
     * @urlParam transfer int required The ID of the transfer. Example: 1
     * @response 200 {
     * "data": {
     * "id": 1,
     * "payer_wallet_id": 2,
     * "payee_wallet_id": 3,
     * "amount": 150.00,
     * "transfer_type": "P2P",
     * "created_at": "2025-12-23T11:40:00Z"
     * }
     * }
     */
    public function show(Transfer $transfer)
    {
        return TransferResource::make($transfer);
    }
}
