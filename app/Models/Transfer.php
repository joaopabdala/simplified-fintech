<?php

namespace App\Models;

use App\Http\Enums\TransferTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
