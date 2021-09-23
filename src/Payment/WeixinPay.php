<?php

namespace Yijin\Pay\Payment;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

/**
 * 等微信官方付款码支付升级v3后，在将代码切换到新版本接口
 */
class WeixinPay extends Base
{
    use Response;

    private $client;

    public function __construct(Config $config)
    {
        parent::__construct($config);
        $this->client = new Client([
            'base_uri' => 'https://api.mch.weixin.qq.com'
        ]);
    }

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        try {
            $res = $this->client->post('pay/micropay', [
                RequestOptions::BODY => $this->generateRequestParams([
                    'auth_code' => $this->config->authCode,
                    'attach' => $this->config->attach,
                    'body' => $this->config->subject,
                    'total_fee' => intval($this->config->totalAmount * 100),
                    'out_trade_no' => $this->config->tradeNo,
                ])
            ]);
            $data = $this->xml2Array($res->getBody()->getContents());
            if ($this->isReturnSuccess($data)) {
                $trade_status = 1;
            } else if ($this->isPaying($data)) {
                $trade_status = 0;
            } else {
                $trade_status = -1;
            }
            return $this->success(array_merge($data, compact('trade_status')));
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        $time = Carbon::createFromTimestamp($this->config->expireTime ?: (time()+ 600));
        try {
            $res = $this->client->post('pay/unifiedorder', [
                RequestOptions::BODY => $this->generateRequestParams([
                    'body' => $this->config->subject,
                    'attach' => $this->config->attach,
                    'out_trade_no' => $this->config->tradeNo,
                    'time_expire' => $time->format('YmdHis'),
                    'notify_url' => $this->config->notifyUrl,
                    'total_fee' => intval($this->config->totalAmount * 100),
                    'product_id' => '123456789',
                    'trade_type' => 'NATIVE',
                ])
            ]);
            $data = $this->xml2Array($res->getBody()->getContents());
            if ($this->isReturnSuccess($data)) {
                return $this->success(array_merge($data, ['payUrl' => $data['code_url']]));
            } else {
                return $this->error($data['err_code_des'] ?? ($data['return_msg'] ?? '请求失败'), -1);
            }
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        try {
            $data = [
                'body' => $this->config->subject,
                'attach' => $this->config->attach,
                'out_trade_no' => $this->config->tradeNo,
                'time_expire' => Carbon::createFromTimestamp($this->config->expireTime ?: (time()+ 600))->format('YmdHis'),
                'notify_url' => $this->config->notifyUrl,
                'total_fee' => intval($this->config->totalAmount * 100),
                'product_id' => '123456789',
                'trade_type' => 'JSAPI',
            ];
            if ($this->config->subAppId) {
                $data['sub_openid'] = $this->config->userId;
            } else {
                $data['openid'] = $this->config->userId;
            }
            $res = $this->client->post('pay/unifiedorder', [
                RequestOptions::BODY => $this->generateRequestParams($data)
            ]);
            $data = $this->xml2Array($res->getBody()->getContents());
            if ($this->isReturnSuccess($data)) {
                $jsApiParameters = [
                    'appId' => $this->config->subAppId ?: $this->config->appid,
                    'timeStamp' => time(),
                    'nonceStr' => md5(time()),
                    'package' => 'prepay_id=' . ($data['prepay_id'] ?? ''),
                    'signType' => 'MD5',
                ];
                $jsApiParameters['paySign'] = $this->sign($jsApiParameters);
                return $this->success(array_merge($data, compact('jsApiParameters')));
            } else {
                return $this->error($data['err_code_des'] ?? ($data['return_msg'] ?? '请求失败'), -1);
            }
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        try {
            $res = $this->client->post('pay/orderquery', [
                RequestOptions::BODY => $this->generateRequestParams([
                    'out_trade_no' => $this->config->tradeNo,
                ])
            ]);
            $data = $this->xml2Array($res->getBody()->getContents());
            if ($this->isReturnSuccess($data)) {
                if (!isset($data['trade_state'])) {
                    $trade_status = -1;
                } else if ($data['trade_state'] === 'SUCCESS'){
                    $trade_status = 1;
                } else if (in_array($data['trade_state'], ['NOTPAY', 'USERPAYING', 'ACCEPT'])){
                    $trade_status = 0;
                } else {
                    $trade_status = -1;
                }
            } else {
                $trade_status = -1;
            }
            return $this->success(array_merge($data, compact('trade_status')));
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @todo 暂不支持部分退款
     * @inheritDoc
     */
    function refund()
    {
        try {
            $res = $this->client->post('secapi/pay/refund', [
                RequestOptions::BODY => $this->generateRequestParams([
                    'out_trade_no' => $this->config->tradeNo,
                    'out_refund_no' => $this->config->refundTradeNo,
                    'total_fee' => intval($this->config->totalAmount * 100),
                    'refund_fee' => intval($this->config->totalAmount * 100),
                    "notify_url"=> $this->config->notifyUrl, //回调推送地址
                ]),
                RequestOptions::SSL_KEY => $this->config->clientApiV2KeyFilePath,
                RequestOptions::CERT => $this->config->clientApiV2CertFilePath
            ]);
            $data = $this->xml2Array($res->getBody()->getContents());
            if ($this->isReturnSuccess($data)) {
                $refund_status = 1;
            } else {
                $refund_status = 0;
            }
            return $this->success(array_merge($data, compact('refund_status')));
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        try {
            $res = $this->client->post('pay/refundquery', [
                RequestOptions::BODY => $this->generateRequestParams([
                    'out_refund_no' => $this->config->refundTradeNo,
                    'out_trade_no' => $this->config->tradeNo,
                ])
            ]);
            $data = $this->xml2Array($res->getBody()->getContents());
            if ($this->isReturnSuccess($data)) {
                $refund_status = 1;
            } else if (isset($data['err_code']) && $data['err_code'] === 'SYSTEMERROR') {
                $refund_status = 0;
            } else {
                $refund_status = -1;
            }
            return $this->success(array_merge($data, compact('refund_status')));
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function notify($data)
    {
        $data = $this->xml2Array($data);
        if (!$this->verifySign($data)) {
            return $this->error('验签失败', -1);
        }
        if (isset($data['return_code']) && $data['return_code'] === 'SUCCESS') {
            $merchantTradeNo = $data['out_refund_no'] ?? ($data['out_trade_no'] ?? '');
            return $this->success(array_merge($data, compact('merchantTradeNo')));
        } else {
            return $this->error($data['return_msg'] ?? '异步失败', $data['return_code'] ?? '-1');
        }
    }

    /**
     * @inheritDoc
     */
    function notifySuccess()
    {
        return [
            'return_code' => 'SUCCESS',
            'return_msg' => 'OK'
        ];
    }

    /**
     * @inheritDoc
     */
    function sign($data): string
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $strArr = [];
        foreach ($data as $key => $item) {
            if ($key != 'sign' && $item) {
                $strArr[] = $key . '=' . $item;
            }
        }
        //签名步骤二：在string后加入KEY
        $string = implode('&', $strArr) . "&key=" . $this->config->apiV2Key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        return strtoupper($string);
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        $sign = $data['sign'] ?? '';
        return $this->sign($data) === $sign;
    }

    private function isReturnSuccess(array $data):bool {
        return isset($data['return_code']) && $data['return_code'] === 'SUCCESS' && isset($data['result_code']) && $data['result_code'] === 'SUCCESS';
    }

    private function isPaying(array $data):bool {
        return isset($data['return_code']) && $data['return_code'] === 'SUCCESS' && isset($data['result_code']) && $data['result_code'] === 'FAIL' && isset($data['err_code']) && in_array($data['err_code'], ['SYSTEMERROR', 'BANKERROR', 'USERPAYING']);
    }

    private function generateRequestParams(array $params): string
    {
        $arr = array_filter(array_merge($this->getCommonParams(), $params, $this->config->optional));
        $arr['sign'] = $this->sign($arr);
        return $this->array2Xml($arr);
    }

    private function getCommonParams(): array
    {
        return [
            'appid' => $this->config->appid,
            'mch_id' => $this->config->mchId,
            'sub_appid' => $this->config->subAppId,
            'sub_mch_id' => $this->config->subMchId,
            'spbill_create_ip' => $this->config->userIP,
            'nonce_str' => md5(time())
        ];
    }

    private function array2Xml($arr): string
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    private function xml2Array($xml): array
    {
        try {
            libxml_disable_entity_loader(true);
            $res = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (\Exception $e) {
            $res = ['return_code' => 'FAIL', 'return_msg' => 'xml解析失败，' . $e->getMessage()];
        }
        return $res ?: ['return_code' => 'FAIL', 'return_msg' => 'xml解析失败'];
    }
}
