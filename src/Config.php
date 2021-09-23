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
 * @property string $appid 微信支付时发起支付的公众号/小程序的APP ID/支付宝直连的APPID
 * @property int $isMiniProgram webPay是不是由小程序发起，1-小程序，0-公众号/服务窗/js支付
 * @property string $userId 用户在微信/支付宝中的id，即微信的openid，支付宝的buyer_id .etc
 * @property int $expireTime 订单有效截止10位（秒级）时间戳
 * @property string $userIP 请求IP
 *
 * 支付宝官方配置
 * @property string $appAuthToken ISV服务商模式下的授权token
 * @property string $merchantPrivateKey 应用私钥，例如：MIIEvQIBADANB
 * @property string $alipayCertPath 支付宝公钥证书文件路径，例如：/foo/alipayCertPublicKey_RSA2.crt
 * @property string $alipayRootCertPath 支付宝根证书文件路径，例如：/foo/alipayRootCert.crt
 * @property string $merchantCertPath 应用公钥证书文件路径，例如：/foo/appCertPublicKey_2019051064521003.crt
 * @property string $alipayPublicKey 支付宝公钥，例如：MIIBIjANBg，非证书模式，填写这个公钥即可，上面三个证书可以不填写
 * @property string $encryptKey AES密钥，调用AES加解密相关接口时需要，非必填
 *
 * 微信官方配置
 * @property string $mchId 商户号
 * @property string $subAppId 子商户的公众号/小程序的APP ID
 * @property string $subMchId 子商户号
 * @property string $apiV2Key 商户API v2密钥
 * @property string $clientApiV2KeyFilePath 商户API v2证书
 * @property string $clientApiV2CertFilePath 商户API v2证书密钥
 * @property string $attach 附加数据，不建议使用
 *
 * 随行付配置
 * @property string $orgIdSxf 服务商机构编号
 * @property string $merchantNoSxf 商户编号
 * @property string $orgPrivateRSAKeySxf 服务商机构RSA私钥内容
 * @property string $orgPublicRSAKeySxf 平台RSA公钥内容
 * @property string $outFrontUrlSxf H5支付后跳转网页地址
 * @property string $wechatFoodOrderSxf 微信扫码点餐标识，最大长度32位,目前需上送：FoodOrder
 * @property string $refundReasonSxf 退款原因。默认值：商家与消费者协商一致
 *
 * 收钱吧配置
 * @property string $serviceProviderIDSqb 服务商ID
 * @property string $activateCodeSqb 激活码
 * @property string $activateDeviceIDSqb 激活设备ID
 * @property string $terminalSNSqb 终端账号
 * @property string $terminalKeySqb 终端密钥
 * @property string $deviceIdSqb 设备唯一ID
 * @property string $operatorSqb 操作员
 * @property string $returnUrlSqb web支付后的跳回地址
 * @property string $reflectSqb web支付后的反射参数
 *
 * 付呗配置
 * @property string $merchantIdFb 商户ID
 * @property string $merchantKeyFb 商户密码
 * @property string $storeIdFb 商户门店ID
 * @property string $wxOpenIDFb 付呗网页下的用户ID
 *
 * 联拓配置
 * @property string $userNameLT 商户后台登录账号
 * @property string $userPwdLt 商户后台登录密码
 * @property string $appIdLt 合作方ID
 * @property string $appKeyLt 签名密钥
 * @property string $merchantCodeLt 商户编号
 * @property string $refundReasonLt 退款原因。默认值：商家与消费者协商一致
 *
 * 乐刷配置
 * @property string $merchantIdLS 商户ID
 * @property string $serviceProviderKeyLS 服务商密码
 * @property string $jumpUrlLS 使用乐刷收银台支付后跳回地址
 */
class Config
{
    const PAY_BY_LT = 1;
    const PAY_BY_FB = 4;
    const PAY_BY_OFFICIAL = 5;
    const PAY_BY_SQB = 6;
    const PAY_BY_LS = 7;
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

    /**
     * 更多的参数想要传递给支付渠道的，可以放入这个数组，会在请求时合并到请求参数内
     * @var array
     */
    public $optional = [];

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
