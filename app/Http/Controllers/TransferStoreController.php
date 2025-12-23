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

class TransferStoreController extends Controller
{
    /**
     * Handle the incoming request.
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
                'error' => 'Internal Server Error. Please try again later',
            ], 500);
        }

        return TransferResource::make($transfer);
    }
}
