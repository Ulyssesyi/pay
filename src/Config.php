<?php

namespace Yijin\Pay;


/**
 * 通用配置参数
 * @property int $channel 支付渠道 1-联付通，4-付呗，5-官方直连，6-收钱吧，7-乐刷，8-云闪付，10-随行付，11-乐天成
 * @property int $payType 支付通道 1-支付宝， 2-微信，3-银联
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
 *
 * 随行付配置
 * @property string $orgId 企业编号
 * @property string $merchantNo 商户编号
 * @property string $userIP 请求IP
 * @property string $orgPrivateRSAKey 机构RSA私钥内容
 * @property string $orgPublicRSAKey 平台RSA公钥内容
 * @property string $outFrontUrl H5支付后跳转网页地址
 * @property string $wechatFoodOrder 微信扫码点餐标识，最大长度32位,目前需上送：FoodOrder
 * @property string $refundReason 退款原因。默认值：商家与消费者协商一致
 */
class Config
{
    const PAY_BY_SXF = 10;

    const ALIPAY = 1;
    const WE_PAY = 2;

    const PAY_SUCCESS = 1;
    const PAYING = 0;
    const PAY_FAIL = -1;

    const REFUND_SUCCESS = 1;
    const REFUNDING = 0;
    const REFUND_FAIL = -1;

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
