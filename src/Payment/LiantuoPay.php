<?php

namespace Yijin\Pay\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

class LiantuoPay extends Base
{
    use Response;
    // B扫C
    const BARCODE_PAY_URL = 'open/pay';
    // C扫B
    const QRCODE_PAY_URL = 'open/jspay';
    // C扫B
    const WEB_PAY_URL = 'open/precreate';
    // 订单支付结果查询
    const ORDER_QUERY_URL = 'open/pay/query';
    // 退款接口
    const REFUND_URL = 'open/refund';
    // 退款查询接口
    const REFUND_QUERY_URL = 'open/refund/query';

    /**
     * @var string $domain 接口域名
     */
    public $domain = 'https://api.liantuofu.com';

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        $params = [
            //业务参数
            "outTradeNo" => $this->config->tradeNo,
            "totalAmount" => $this->config->totalAmount,
            "authCode" => $this->config->authCode
        ];
        try {
            $res = $this->execRequest($params, self::BARCODE_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $trade_status = Config::PAY_SUCCESS;
        } elseif ($this->isPaying($res)) {
            $trade_status = Config::PAYING;
        } else {
            return $this->error($res['subMsg'] ?? ($res['msg'] ?? '系统异常'), $res['subCode'] ?? ($res['code'] ?? -1));
        }
        $transaction_id = $res['transactionId'] ?? '';
        return $this->success(array_merge($res, compact('trade_status', 'transaction_id')));
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        $params = [
            "outTradeNo" => $this->config->tradeNo,
            "totalAmount" => $this->config->totalAmount
        ];
        try {
            $res = $this->execRequest($params, self::QRCODE_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $payUrl = $res['url'] ?? '';
            return $this->success(array_merge($res, compact('payUrl')));
        } else {
            return $this->error($res['subMsg'] ?? ($res['msg'] ?? '系统异常'), $res['subCode'] ?? ($res['code'] ?? -1));
        }
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        if ($this->config->isMiniProgram) {
            return $this->error('暂未支持小程序', -1);
        }
        $params = [
            "channel" => $this->getPayType(),
            "tradeType" => 'JSAPI',
            "outTradeNo" => $this->config->tradeNo,
            "totalAmount" => $this->config->totalAmount,
            "notifyUrl" => $this->config->notifyUrl,
            "openId" => $this->config->userId,
            "subAppId" => $this->config->appid,
            "subject" => $this->config->subject,
        ];
        try {
            $res = $this->execRequest($params, self::WEB_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            if ($this->config->payType === Config::WE_PAY) {
                $data['jsApiParameters'] = [
                    'appId' => $res['appId'] ?? '',
                    'timeStamp' => $res['timeStamp'] ?? '',
                    'nonceStr' => $res['nonceStr'] ?? '',
                    'paySign' => $res['paySign'] ?? '',
                    'package' => $res['payPackage'] ?? '',
                    'signType' => $res['signType'] ?? '',
                ];
            } else {
                $data['trade_no'] = $res['transactionId'] ?? '';
            }
            return $this->success(array_merge($res, $data));
        } else {
            return $this->error($res['subMsg'] ?? ($res['msg'] ?? '系统异常'), $res['subCode'] ?? ($res['code'] ?? -1));
        }
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        $params = [
            "outTradeNo" => $this->config->tradeNo,
        ];

        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $trade_status = Config::PAY_SUCCESS;
        } elseif ($this->isPaying($res)) {
            $trade_status = Config::PAYING;
        } else {
            return $this->error($res['subMsg'] ?? ($res['msg'] ?? '系统异常'), $res['subCode'] ?? ($res['code'] ?? -1));
        }
        $transaction_id = $res['transactionId'] ?? '';
        return $this->success(array_merge($res, compact('trade_status', 'transaction_id')));
    }

    /**
     * @inheritDoc
     */
    function refund()
    {
        $params = [
            "outTradeNo" => $this->config->tradeNo,
            "refundNo" => $this->config->refundTradeNo,
            "refundAmount" => $this->config->totalAmount,
            "refundReason" => $this->config->refundReasonLt,
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $refund_status = Config::REFUND_SUCCESS;
        } elseif ($this->isRefunding($res)) {
            $refund_status = Config::REFUNDING;
        } else {
            return $this->error($res['subMsg'] ?? ($res['msg'] ?? '系统异常'), $res['subCode'] ?? ($res['code'] ?? -1));
        }
        return $this->success(array_merge($res, compact('refund_status')));
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        $params = [
            "refundNo" => $this->config->refundTradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $refund_status = Config::REFUND_SUCCESS;
        } elseif ($this->isRefunding($res)) {
            $refund_status = Config::REFUNDING;
        } else {
            return $this->error($res['subMsg'] ?? ($res['msg'] ?? '系统异常'), $res['subCode'] ?? ($res['code'] ?? -1));
        }
        return $this->success(array_merge($res, compact('refund_status')));
    }

    /**
     * @inheritDoc
     */
    function notify($data)
    {
        if (!$this->verifySign($data)) {
            return $this->error('验签失败', -1);
        }
        if ($this->isSuccess($data)) {
            $merchantTradeNo = $data['outTradeNo'] ?? '';
            $transaction_id = $data['transactionId'] ?? '';
            return $this->success(array_merge($data, compact('merchantTradeNo', 'transaction_id')));
        } else {
            return $this->error($res['subMsg'] ?? ($res['msg'] ?? '系统异常'), $res['subCode'] ?? ($res['code'] ?? -1));
        }
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
        $data = array_filter($data);
        ksort($data);
        $signString = "";
        foreach ($data as $key => $val) {
            if ($key !== 'sign' && $key !== 'partner_key') {
                $signString .= $key . "=" . $val . "&";
            }
        }
        $signString .= 'key=' .  $this->config->appKeyLt;
        return strtolower(md5($signString));
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        $sign = $data['sign'];
        return $sign === $this->sign($data);
    }

    /**
     * @throws GuzzleException
     */
    private function execRequest($params, $url) {
        $commonParams = array_merge([
            "appId" => $this->config->appIdLt,
            "merchantCode" => $this->config->merchantCodeLt,
            "random" => time(),
        ], $params, $this->config->optional);
        $commonParams['sign'] = $this->sign($commonParams);

        $client = new Client([
            'base_uri' => $this->domain,
            'timeout' => $this->config->requestTimeout ?? 10
        ]);
        $response = $client->post($url, [
            'form_params' => $commonParams
        ]);
        $responseData = $response->getBody()->getContents();
        return json_decode($responseData, true);
    }

    private function isSuccess($data): bool
    {
        return isset($data['code']) && $data['code'] == 'SUCCESS';
    }

    private function isPaying($data): bool
    {
        return isset($data['subCode']) && $data['subCode'] == 'USER_PAYING';
    }

    private function isRefunding($data): bool
    {
        return isset($data['subCode']) && $data['subCode'] == 'REFUNDING';
    }

    public function auth(): array
    {
        $client = new Client([]);
        try {
            $response = $client->post('https://api.liantuofu.com/open/login', [
                'form_params' => [
                    'userName' => $this->config->userNameLT,
                    'passWord' => $this->config->userPwdLt,
                ]
            ]);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        $responseData = $response->getBody()->getContents();
        $res = $responseData ? json_decode($responseData, true) : [];
        if ($this->isSuccess($res)) {
            return $this->success([
                'appId' => $res['appId'] ?? '',
                'appKey' => $res['key'] ?? '',
                'merchantCode' => $res['merchantCode'] ?? '',
            ]);
        } else {
            return  $this->error($res['subMsg'] ?? ($res['msg'] ?? '请求异常'), $res['subCode'] ?? ($res['code'] ?? -1));
        }
    }

    private function getPayType(): string
    {
        switch ($this->config->payType) {
            case Config::WE_PAY:
                return 'WXPAY';
            default:
                return 'ALIPAY';
        }
    }
}
