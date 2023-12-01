<?php

namespace Yijin\Pay\Enums;

class TransactionType
{
    const BARCODE_PAY = 1;
    const QRCODE_PAY = 2;
    const WEB_PAY = 3;
    const QUERY = 4;
    const REFUND = 5;
    const REFUND_QUERY = 6;
}
