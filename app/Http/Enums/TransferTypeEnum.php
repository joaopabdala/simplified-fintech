<?php

namespace App\Http\Enums;

enum TransferTypeEnum: string
{
    case SHOP_PAYMENT = 'shop_payment';
    case USER_PAYMENT = 'user_payment';
    case DEPOSIT = 'deposit';
}
