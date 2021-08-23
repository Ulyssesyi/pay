<?php

namespace Yijin\Pay\Config;

/**
 * @property string $orgId 企业编号
 * @property string $merchantNo 商户编号
 * @property string $userIP 请求IP
 * @property string $orgPrivateRSAKey RSA密钥内容
 * @property string $outFrontUrl H5支付后跳转网页地址
 * @property string $wechatFoodOrder 微信扫码点餐标识，最大长度32位,目前需上送：FoodOrder
 * @property string $refundReason 退款原因。默认值：商家与消费者协商一致
 */
class SxfConfig extends Base
{
    /**
     * @var string $domain 接口域名
     */
    public $domain = 'https://openapi.tianquetech.com';
    // B扫C
    const BARCODE_PAY_URL = 'order/reverseScan';
    // C扫B
    const QRCODE_PAY_URL = 'order/activeScan';
    // js网页支付URL
    const JS_PAY_URL = 'order/jsapiScan';
    // 订单支付结果查询
    const ORDER_QUERY_URL = 'query/tradeQuery';
    // 退款接口
    const REFUND_URL = 'order/refund';
    // 退款查询接口
    const REFUND_QUERY_URL = 'query/refundQuery';
}
