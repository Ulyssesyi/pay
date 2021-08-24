<?php
namespace Yijin\Pay\Config;

/**
 * @property int channel 支付通道 1-支付宝， 2-微信，3-银联
 * @property string $charset 请求和返回编码，目前都是UTF-8
 * @property string $tradeNo 商户订单号
 * @property string $refundTradeNo 商户退款订单号
 * @property float $totalAmount 订单总金额
 * @property string $subject 订单标题
 * @property string $authCode B扫C时读取到的条码内容
 * @property string $notifyUrl 支付结果异步通知地址
 * @property string $appid 微信支付时发起支付的公众号/小程序的APP ID
 * @property int $isMiniProgram webPay是不是由小程序发起，1-小程序，0-公众号/服务窗/js支付
 * @property string $userId 用户在微信/支付宝中的id，即微信的openid，支付宝的buyer_id .etc
 */
class Base
{
    public static $ALIPAY = 1;
    public static $WE_PAY = 2;

    protected $_config = [];

    public function __set($name, $value)
    {
        $this->_config[$name] = $value;
    }

    public function __get($name)
    {
        return  $this->_config[$name] ?? null;
    }

    public function __toString()
    {
        return json_encode($this->_config);
    }

    public function __serialize(): array
    {
        return $this->_config;
    }
}
