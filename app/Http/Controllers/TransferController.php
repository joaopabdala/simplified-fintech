<?php

namespace App\Http\Controllers;

use App\Http\Enums\TransferTypeEnum;
use App\Http\Enums\UserTypeEnum;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransferResource;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function response;

class TransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transfers = Transfer::orderByDesc('created_at')->paginate(5);

        return TransferResource::collection($transfers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransferRequest $request)
    {
        $payer = User::findOrFail($request->payer);
        $payee = User::findOrFail($request->payee);
        $value = $request->value;

        try {
            $transfer = DB::transaction(function () use ($payer, $payee, $value) {
                if ($payer->user_type == UserTypeEnum::SHOP) {
                    throw new \InvalidArgumentException("Shop type users can't do transfers");
                }
                $transferType = $payee->user_type == UserTypeEnum::COMMON
                    ? TransferTypeEnum::USER_PAYMENT
                    : TransferTypeEnum::SHOP_PAYMENT;

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

                return Transfer::create([
                    'payer_wallet_id' => $payerWallet->id,
                    'payee_wallet_id' => $payeeWallet->id,
                    'amount' => $value,
                    'transfer_type' => $transferType,
                ]);
            });
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'error' => 'Internal Server Error. Please try again later'
            ], 500);
        }
        return TransferResource::make($transfer);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transfer $transfer)
    {
        return TransferResource::make($transfer);
    }
}
