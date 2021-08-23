<?php

namespace Yijin\Pay\Payment;

use \Yijin\Pay\Config\Base as Config;

abstract class Base
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * 条码支付（B扫C）
     * @return mixed
     */
    abstract function barcodePay();
    /**
     * 二维码支付（C扫B）
     * @return mixed
     */
    abstract function qrcodePay();
    /**
     * 网页支付（含H5和小程序支付）
     * @return mixed
     */
    abstract function webPay();

    /**
     * 支付结果查询
     * @return mixed
     */
    abstract function query();

    /**
     * 支付退款
     * @return mixed
     */
    abstract function refund();

    /**
     * 支付退款查询
     * @return mixed
     */
    abstract function refundQuery();

    /**
     * 签名
     */
    abstract function sign($data);

    /**
     * 验证签名
     */
    abstract function verifySign(array $data): bool;
}
