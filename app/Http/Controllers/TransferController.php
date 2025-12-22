<?php

namespace App\Http\Controllers;

use App\Actions\Transfer\HandleTransferAction;
use App\Exceptions\TransferException;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransferResource;
use App\Models\Transfer;
use App\Models\User;
use Exception;
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
    public function store(TransferRequest $request, HandleTransferAction $handleTransferAction)
    {
        $payer = User::findOrFail($request->payer);
        $payee = User::findOrFail($request->payee);
        $value = $request->value;

        try {
            $transfer = $handleTransferAction->execute($payer, $payee, $value);
        } catch (TransferException $e) {
            Throw $e;

        } catch (Exception $e) {
            Log::error('Transfer store error: ' . $e->getMessage());
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
