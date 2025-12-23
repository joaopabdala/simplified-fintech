<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TransferException extends Exception
{
    public function render($request): JsonResponse
    {
        return response()->json([
            'error' => $this->getMessage(),
        ], $this->getCode() ?: 422);
    }

    public static function InsufficientBalance(): self
    {
        return new self('Insufficient balance.', 422);
    }

    public static function ShopTypeUsersCantTransfer(): self
    {
        return new self("Shop type users can't do transfers.", 422);
    }

    public static function NotAuthorized(): self
    {
        return new self('Not authorized.', 403);
    }
}
