<?php

namespace Yijin\Pay\Payment;

use \Yijin\Pay\Config;

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
     * 返回的data中必须包含key-trade_status：当前交易状态，-1-支付失败, 0-支付进行中, 1-支付完成
     * @return mixed
     */
    abstract function barcodePay();
    /**
     * 二维码支付（C扫B）
     * 返回的data中必须包含key-payUrl：二维码的图片地址/二维码的base64编码
     * @return mixed
     */
    abstract function qrcodePay();
    /**
     * 网页支付（含H5和小程序支付）
     * 返回的data中微信必须包含key-jsApiParameters：H5和小程序支付的参数数组，里面key list为appId/timeStamp/nonceStr/package/signType；支付宝必须包含key-trade_no：支付宝网页呼起支付用的订单号
     * 如果是跳转支付的必须包含key-payUrl：支付跳转的地址
     * @return mixed
     */
    abstract function webPay();

    /**
     * 支付结果查询
     * 返回的data中必须包含key-trade_status：当前交易状态，-1-支付失败, 0-支付进行中, 1-支付完成
     * @return mixed
     */
    abstract function query();

    /**
     * 支付退款
     * 返回的data中必须包含key-refund_status：当前退款状态，-1-退款失败, 0-退款进行中, 1-退款完成
     * @return mixed
     */
    abstract function refund();

    /**
     * 支付退款查询
     * 返回的data中必须包含key-refund_status：当前退款状态，-1-退款失败, 0-退款进行中, 1-退款完成
     * @return mixed
     */
    abstract function refundQuery();

    /**
     * 异步通知处理，返回的result为true代表异步通知的结果是成功
     * 返回的data中必须包含key-merchantTradeNo：商户订单号/商户退款单号
     * @return mixed
     */
    abstract function notify($data);

    /**
     * 异步通知处理成功后给第三方平台的成功返回
     * @return mixed
     */
    abstract function notifySuccess();

    /**
     * 签名
     */
    abstract function sign($data);

    /**
     * 验证签名
     */
    abstract function verifySign(array $data): bool;
}
