<?php

namespace Yijin\Pay;


/**
 * 通用配置参数
 * @property int $channel 支付渠道 16-KBZ支付 9-IPay88 Pay 13-Hi Pay 14-Stripe Pay
 * @property string $charset 请求和返回编码，目前都是UTF-8
 * @property string $tradeNo 商户订单号
 * @property string $refundTradeNo 商户退款订单号
 * @property float $totalAmount 订单总金额
 * @property string $subject 订单标题
 * @property string $authCode B扫C时读取到的条码内容
 * @property string $notifyUrl 支付结果异步通知地址
 * @property bool $isSandbox 是否UAT环境
 *
 * KBZPay参数
 * @property string $kbzAppId 商户的应用id
 * @property string $kbzMerchantCode 商户编码
 * @property string $kbzMerchantKey 商户密钥
 *
 * HiPay参数
 * @property string $hipayAppId 商户的应用id
 * @property string $hiPayPrivateKey 商户私钥
 * @property string $hiPayPublicKey 应用公钥
 *
 * IPay88参数
 * @property string $iPay88MerchantKey 商户key
 * @property string $iPay88MerchantCode 商户编码
 * @property string $iPay88MerchantName 商户名称
 * @property string $iPay88MerchantContact 商户手机号
 * @property string $iPay88MerchantEmail 商户邮箱
 *
 * GKash参数
 * @property string $gKashCID 商户的应用id
 * @property string $gKashSignKey 商户密钥
 *
 * MayBank参数
 * @property string $mayBankMerchantCode 商户code
 *
 * Stripe参数
 * @property string $stripePublicKey 应用公钥
 * @property string $stripePrivateKey 应用私钥
 * @property string $stripeEndKey 平台公钥
 * @property string $stripeAccount 商户账号
 * @property string $stripePaymentMethod 支付方式id，网页支付需要
 * @property string $stripePaymentMethodType 支付方式，网页支付需要
 * @property string $stripePaymentIntentId 支付id，刷卡支付需要
 *
 */
class AbroadConfig
{
    const PAY_BY_HIPAY = 13;
    const PAY_BY_IPAY88 = 9;
    const PAY_BY_GKASH = 16;
    const PAY_BY_STRIPE = 14;
    const PAY_BY_MAY_BANK = 17;

    const PAY_SUCCESS = 1;
    const PAYING = 0;
    const PAY_FAIL = -1;

    const REFUND_SUCCESS = 1;
    const REFUNDING = 0;
    const REFUND_FAIL = -1;

    protected array $_config = [];

    /**
     * 更多的参数想要传递给支付渠道的，可以放入这个数组，会在请求时合并到请求参数内
     * @var array
     */
    public array $optional = [];

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
