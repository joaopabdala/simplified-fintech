<?php

namespace App\Models;

use App\Http\Enums\TransferTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'payer_wallet_id',
        'payee_wallet_id',
        'amount',
        'transfer_type'
    ];

    protected $casts = [
        'transfer_type' => TransferTypeEnum::class
    ];
}
