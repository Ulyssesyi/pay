<?php

namespace Yijin\Pay\Payment;

use GuzzleHttp\Client;
use Yijin\Pay\Config\SxfConfig as Config;

class SxfPay extends Base
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        $params = [
            //业务参数
            "mno" => $this->config->merchantNo, //商户编号
            "ordNo" => $this->config->tradeNo, //商户订单号
            //"subMechId"=> "", //子商户号
            //"subAppid"=> "", //微信 subAppId
            "amt" => $this->config->totalAmount, //订单总金额
            "authCode" => $this->config->authCode, //授权码
            //"discountAmt"=> "", //参与优惠金额
            //"unDiscountAmt"=> "", //不参与优惠金额
            // "payType" => $this->getPayMethod(), //支付渠道
            "scene" => "1", ////支付场景，1： 刷卡 2：声波 3：刷脸   不上传默认为 1
            "subject" => $this->config->subject, // 订单描述
            "tradeSource" => "01", //交易来源 01服务商，02收银台，03硬件
            "trmIp" => $this->config->userIP,
            //"hbFqNum"=> "6", //花呗分期数,仅可上送 6 或 12
            //"hbFqPercent"=> "0", //卖家承担分期 服务费比例,仅支持上送 0 或 100
            //"limitPay"=> "00", //限制卡类型: 00-全部 01-限定不能使 用信用卡支付 默认值 00
            //"timeExpire"=> "10", //订单失效时间
            //"goodsTag"=> "00", //订单优惠标识 00：是，01： 否
            //"couponDetail"=> "", //优惠详情信息，见下面三个字段
            //"costPrice"=> "200", //订单原价保留两 位小数；微信 独有
            //"receiptId"=> "123456789", //商品小票
            //"goodsDetail"=> "123456789", //单品优惠信息使用 json 数组格式提交
            //"goodsId"=> "200", //商品编码
            //"thirdGoodsId"=> "12345678", //微信/支付宝侧商品码
            //"goodsName"=> "苹果电脑", //商品名称
            //"quantity"=> "1", //商品数量
            //"price"=> "1.01", //商品单价
            //"goodsCategory"=> "", //商品类目；支 付宝独有
            //"categoriesTree"=> "124868003|126232002|126252004", //商品类目树
            //"goodsDesc"=> "", //商品描述；支 付宝独有
            //"showUrl"=> "", //商品展示地址 url；支付宝独有
            //"needReceipt"=> "00", //电子发票功能 微信开具电子 发票使用
            //"ledgerAccountFlag"=> "00", //是否做分账 分账交易使 用；00：做； 01：不做；不传默认为不做分账
            //"ledgerAccountEffectTime"=> "00", //分账有效时间 单位为天；是 否做分账选择 00 时该字段必传
            "notifyUrl"=> $this->config->notifyUrl, //回调地址
            //"ylTrmNo"=> "", //银联终端号
            //"terminalId"=> "", //TQ机具编号
            //"deviceNo"=> "", //设备号
            //"identityFlag"=> "", //是否是实名支付
            //"buyerIdType"=> "IDCARD", //证件类型
            //"buyerIdNo"=> "410523198701054018", //证件号
            //"buyerName"=> "张三", //买家姓名
            //"mobileNum"=> "", //手机号
            //"extend"=> "" //备用
        ];
        return $this->execRequest($params, Config::BARCODE_PAY_URL);
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        $params = [
            "mno" => $this->config->merchantNo, //商户编号
            "ordNo" => $this->config->tradeNo, //商户订单号
            //"subMechId"=> "", //子商户号
            //"subAppid"=> "", //微信 subAppId
            "amt" => $this->config->totalAmount, //订单总金额
            //"discountAmt"=> "", //参与优惠金额
            //"unDiscountAmt"=> "", //不参与优惠金额
            "payType" => $this->getPayChannel(), //支付渠道
            "subject" => $this->config->subject,
            "tradeSource" => "01", //交易来源 01服务商，02收银台，03硬件
            "trmIp" => $this->config->userIP,
            //"hbFqNum"=> "6", //花呗分期数,仅可上送 6 或 12
            //"hbFqPercent"=> "0", //卖家承担分期 服务费比例,仅支持上送 0 或 100
            //"limitPay"=> "00", //限制卡类型: 00-全部 01-限定不能使 用信用卡支付 默认值 00
            //"timeExpire"=> "10", //订单失效时间
            //"goodsTag"=> "00", //订单优惠标识 00：是，01： 否
            //"couponDetail"=> "", //优惠详情信息，见下面三个字段
            //"costPrice"=> "200", //订单原价保留两 位小数；微信 独有
            //"receiptId"=> "123456789", //商品小票
            //"goodsDetail"=> "123456789", //单品优惠信息使用 json 数组格式提交
            //"goodsId"=> "200", //商品编码
            //"thirdGoodsId"=> "12345678", //微信/支付宝侧商品码
            //"goodsName"=> "苹果电脑", //商品名称
            //"quantity"=> "1", //商品数量
            //"price"=> "1.01", //商品单价
            //"goodsCategory"=> "", //商品类目；支 付宝独有
            //"categoriesTree"=> "124868003|126232002|126252004", //商品类目树
            //"goodsDesc"=> "", //商品描述；支 付宝独有
            //"showUrl"=> "", //商品展示地址 url；支付宝独有
            //"needReceipt"=> "00", //电子发票功能 微信开具电子 发票使用
            //"ledgerAccountFlag"=> "00", //是否做分账 分账交易使 用；00：做； 01：不做；不传默认为不做分账
            //"ledgerAccountEffectTime"=> "00", //分账有效时间 单位为天；是 否做分账选择 00 时该字段必传
            "notifyUrl"=> $this->config->notifyUrl, //回调地址
            //"ylTrmNo"=> "", //银联终端号
            //"terminalId"=> "", //TQ机具编号
            //"deviceNo"=> "", //设备号
            //"identityFlag"=> "", //是否是实名支付
            //"buyerIdType"=> "IDCARD", //证件类型
            //"buyerIdNo"=> "410523198701054018", //证件号
            //"buyerName"=> "张三", //买家姓名
            //"mobileNum"=> "", //手机号
            //"extend"=> "" //备用
        ];
        return $this->execRequest($params, Config::QRCODE_PAY_URL);
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        $params = [
            //业务参数
            "mno" => $this->config->merchantNo, //商户编号
            "ordNo" => $this->config->tradeNo, //商户订单号
            //"subMechId"=> "", //子商户号
            "subAppid" => $this->config->appid, //微信 subAppId
            "amt" => $this->config->totalAmount, //订单总金额
            //"discountAmt"=> "", //参与优惠金额
            //"unDiscountAmt"=> "", //不参与优惠金额
            "payType" => $this->getPayChannel(), //支付渠道
            "payWay" => $this->config->isMiniProgram ? "03" : "02", //支付方式  02 公众号/服 务窗/js支付 03 小程序
            "subject" => $this->config->subject,
            "tradeSource" => "01", //交易来源 01服务商，02收银台，03硬件
            "trmIp" => $this->config->userIP,
            "customerIp" => $this->config->userIP, //持卡人ip地址，银联js支付时必传
            "userId" => $this->config->userId, //用户号 微信：openid； 支付宝：userid；银联：userid；微信&支付宝必传，银联js为非必传
            //"hbFqNum"=> "6", //花呗分期数,仅可上送 6 或 12
            //"hbFqPercent"=> "0", //卖家承担分期 服务费比例,仅支持上送 0 或 100
            //"limitPay"=> "00", //限制卡类型: 00-全部 01-限定不能使 用信用卡支付 默认值 00
            //"timeExpire"=> "10", //订单失效时间
            //"goodsTag"=> "00", //订单优惠标识 00：是，01： 否
            //"couponDetail"=> "", //优惠详情信息，见下面三个字段
            //"costPrice"=> "200", //订单原价保留两 位小数；微信 独有
            //"receiptId"=> "123456789", //商品小票
            //"goodsDetail"=> "123456789", //单品优惠信息使用 json 数组格式提交
            //"goodsId"=> "200", //商品编码
            //"thirdGoodsId"=> "12345678", //微信/支付宝侧商品码
            //"goodsName"=> "苹果电脑", //商品名称
            //"quantity"=> "1", //商品数量
            //"price"=> "1.01", //商品单价
            //"goodsCategory"=> "", //商品类目；支 付宝独有
            //"categoriesTree"=> "124868003|126232002|126252004", //商品类目树
            //"goodsDesc"=> "", //商品描述；支 付宝独有
            //"showUrl"=> "", //商品展示地址 url；支付宝独有
            //"needReceipt"=> "00", //电子发票功能 微信开具电子 发票使用
            //"ledgerAccountFlag"=> "00", //是否做分账 分账交易使 用；00：做； 01：不做；不传默认为不做分账
            //"ledgerAccountEffectTime"=> "00", //分账有效时间 单位为天；是 否做分账选择 00 时该字段必传
            "notifyUrl"=> $this->config->notifyUrl, //回调地址
            "outFrontUrl"=> $this->config->outFrontUrl, //支付成功后跳转网页地址
            //"ylTrmNo"=> "", //银联终端号
            //"terminalId"=> "", //TQ机具编号
            //"deviceNo"=> "", //设备号
            //"identityFlag"=> "", //是否是实名支付
            //"buyerIdType"=> "IDCARD", //证件类型
            //"buyerIdNo"=> "410523198701054018", //证件号
            //"buyerName"=> "张三", //买家姓名
            //"mobileNum"=> "", //手机号
            //"extend"=> "", //备用
            "wechatFoodOrder"=> $this->config->wechatFoodOrder //微信扫码点餐标识，最大长度32位,目前需上送：FoodOrder
        ];
        return $this->execRequest($params, Config::JS_PAY_URL);
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        $params = [
            //业务参数
            "mno" => $this->config->merchantNo, //商户编号
            //下面三个至少传一个
            "ordNo" => $this->config->tradeNo, //商户订单号
            //"uuid"=> "", //科技公司订单号
            //"transactionId"=> "", //正交易落单号
            //"terminalId"=> "", //TQ 机具编号， 支付来源为硬 件时，该参数 为必传；
            //"deviceNo"=> ""//设备号
        ];
        return $this->execRequest($params, Config::ORDER_QUERY_URL);
    }

    /**
     * @inheritDoc
     */
    function refund()
    {
        $params = [
            //业务参数
            "mno" => $this->config->merchantNo, //商户编号
            "ordNo" => $this->config->refundTradeNo, //商户退款订单号
            //下面三个至少传一个
            "origOrderNo" => $this->config->tradeNo, //原商户订单号
            // "origUuid" => "", //原交易科技公司订单号
            // "origSxfUuid" => "", //正交易落单号
            "amt" => $this->config->totalAmount, //退款金额
            "notifyUrl"=> $this->config->notifyUrl, //回调推送地址
            "refundReason" => $this->config->refundReason ?: "商家与消费者协商一致", //退货原因
            // "extend" => "" //备用
        ];
        return $this->execRequest($params, Config::REFUND_URL);
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        $params = [
            //业务参数
            "mno" => $this->config->merchantNo, //商户编号
            //下面两个至少传一个
            "ordNo" => $this->config->refundTradeNo, //商户退款订单号
            // "uuid"=> "" //科技公司订单号
        ];
        return $this->execRequest($params, Config::REFUND_QUERY_URL);
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    function sign($data): string
    {
        $signContent = $this->generateSignString($data);
        $signSecret = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->config->orgPrivateRSAKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($signContent, $sign, $signSecret);
        return base64_encode($sign);
    }

    /**
     * 验证签名,暂无
     */
    function verifySign(array $data): bool
    {
        $signKey = 'sign';
        $signed = $data[$signKey];
        unset($data[$signKey]);
        $signContent = $this->generateSignString($data);
        $signSecret = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->config->orgPublicRSAKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        return openssl_verify($signContent, base64_decode($signed), $signSecret);
    }

    private function generateSignString($data): string
    {
        ksort($data);
        $stringToBeSigned = "";
        foreach ($data as $k => $v) {
            $isArray = is_array($v);
            if ($isArray) {
                $stringToBeSigned .= "$k" . "=" . json_encode($v, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE) . "&";
            } else {
                $stringToBeSigned .= "$k" . "=" . "$v" . "&";
            }
        }
        unset ($k, $v);
        return mb_substr($stringToBeSigned, 0, mb_strlen($stringToBeSigned) - 1);
    }

    private function execRequest($params, $url) {
        if (isset($params['subject'])) {
            $res = htmlentities($params['subject']);
            if ($res != $params['subject']) {
                $params['subject'] = '前台结算';
            } else {
                $params['subject'] = $res;
            }
        }
        $commonParams = [
            "orgId" => $this->config->orgId,
            "reqData" => array_filter($params),
            "reqId" => uniqid('sxf'),
            "signType" => "RSA",
            "timestamp" => date("Y-m-d h:i:s"),
            "version" => "1.0",
        ];
        $commonParams['sign'] = $this->sign($commonParams);

        $client = new Client([
            'base_uri' => $this->config->domain,
            'curl' => [
                CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1'
            ]
        ]);
        $response = $client->post($url, [
            'json' => $commonParams
        ]);
        if ($response->getStatusCode() >= 400) {
            throw new \Exception("请求{$url}失败，错误码为".$response->getStatusCode());
        }
        $responseData = $response->getBody()->getContents();
        return json_decode($responseData, true);
    }

    private function getPayChannel(): string
    {
        switch ($this->config->channel) {
            case Config::$WE_PAY:
                return 'WECHAT';
            case Config::$ALIPAY:
                return 'ALIPAY';
            default:
                return 'UNIONPAY';
        }
    }
}
