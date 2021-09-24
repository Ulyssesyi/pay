<?php

namespace Yijin\Pay\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

class UnionPay extends Base
{
    use Response;
    // B扫C
    const BARCODE_PAY_URL = 'gateway/api/pay/micropay';
    // C扫B
    const QRCODE_PAY_URL = 'gateway/api/pay/qrpay';
    // h5/小程序支付
    const WEB_PAY_URL = 'gateway/api/pay/unifiedorder';
    // 订单支付结果查询
    const ORDER_QUERY_URL = 'gateway/api/pay/queryOrder';
    // 退款接口
    const REFUND_URL = 'gateway/api/pay/refund';
    // 退款查询接口
    const REFUND_QUERY_URL = 'gateway/api/pay/refundQuery';

    /**
     * @var string $domain 接口域名
     */
    public $domain = 'https://partner.95516.com';

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        return $this->error('暂未上线', -1);
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        return $this->error('暂未上线', -1);
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        return $this->error('暂未上线', -1);
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        return $this->error('暂未上线', -1);
    }

    /**
     * @inheritDoc
     */
    function refund()
    {
        return $this->error('暂未上线', -1);
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        return $this->error('暂未上线', -1);
    }

    /**
     * @inheritDoc
     */
    function notify($data)
    {
        return $this->error('暂未上线', -1);
    }

    /**
     * @inheritDoc
     */
    function notifySuccess()
    {
        return 'success';
    }

    /**
     * @inheritDoc
     */
    function sign($data): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        return false;
    }
}
