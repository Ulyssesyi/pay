<?php

namespace Yijin\Pay\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

class LtcPay extends Base
{
    use Response;
    // B扫C
    const BARCODE_PAY_URL = 'payment/v1/pay/scan';
    // C扫B
    const QRCODE_PAY_URL = 'payment/v1/pay/validity';
    // 网页/小程序支付
    const WEB_PAY_URL = 'payment/v1/pay/validity';
    // 订单支付结果查询
    const ORDER_QUERY_URL = 'payment/v1/order/status';
    // 退款接口
    const REFUND_URL = 'payment/v1/pay/refund';
    // 退款查询接口
    const REFUND_QUERY_URL = 'payment/v1/order/status';

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        $params = [
            //业务参数
            "seq_no" => $this->config->tradeNo,
            "pay_amt" => intval(bcmul($this->config->totalAmount, 100)),
            "auth_code" => $this->config->authCode,
            "call_back_url" => $this->config->notifyUrl
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
            return $this->error($res['ret_msg'] ?? '系统异常', $res['ret_code'] ?? -1);
        }
        $transaction_id = '';
        return $this->success(array_merge($res, compact('trade_status', 'transaction_id')));
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        $params = [
            "seq_no" => $this->config->tradeNo,
            "pay_amt" => intval(bcmul($this->config->totalAmount, 100)),
            "call_back_url" => $this->config->notifyUrl
        ];
        try {
            $res = $this->execRequest($params, self::QRCODE_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $payUrl = 'https://cjcd.leyunykt.com/wxservice/redirect?url=' . urlencode($res['ret_data']);
            return $this->success(array_merge($res, compact('payUrl')));
        } else {
            return $this->error($res['ret_msg'] ?? '系统异常', $res['ret_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        $params = [
            "seq_no" => $this->config->tradeNo,
            "pay_amt" => intval(bcmul($this->config->totalAmount, 100)),
            "call_back_url" => $this->config->notifyUrl,
            "notify_url" => $this->config->jumpUrlLtc,
        ];
        try {
            $res = $this->execRequest($params, self::WEB_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $payUrl = $res['ret_data'] ?? '';
            return $this->success(array_merge($res, compact('payUrl')));
        } else {
            return $this->error($res['ret_msg'] ?? '系统异常', $res['ret_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        $params = [
            "seq_no" => $this->config->tradeNo,
        ];

        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $data = json_decode($res['ret_data'], true);
            $status = $data['pay_sts'] ?? '';
            switch ($status) {
                case '已支付':
                    $trade_status = Config::PAY_SUCCESS;
                    break;
                case '支付失败':
                    $trade_status = Config::PAY_FAIL;
                    break;
                default:
                    $trade_status = Config::PAYING;
            }
            $transaction_id = '';
            return $this->success(array_merge($res, compact('trade_status', 'transaction_id')));
        } else {
            return $this->error($res['ret_msg'] ?? '系统异常', $res['ret_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function refund()
    {
        $params = [
            "seq_no" => $this->config->tradeNo,
            "amount" => intval(bcmul($this->config->totalAmount, 100)),
            "ref_date" => date('Y-m-d H:i:s')
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $refund_status = Config::REFUNDING;
            return $this->success(array_merge($res, compact('refund_status')));
        } else {
            return $this->error($res['ret_msg'] ?? '系统异常', $res['ret_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        $params = [
            "seq_no" => $this->config->tradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $data = json_decode($res['ret_data'], true);
            $status = $data['pay_sts'] ?? '';
            switch ($status) {
                case '已退款':
                    $refund_status = Config::REFUND_SUCCESS;
                    break;
                case 'REFUND_PROCESSING':
                    $refund_status = Config::REFUNDING;
                    break;
                default:
                    $refund_status = Config::REFUND_FAIL;
            }
            return $this->success(array_merge($res, compact('refund_status')));
        } else {
            return $this->error($res['ret_msg'] ?? '系统异常', $res['ret_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function notify($data)
    {
        list($notify_data, $sign) = explode('SIGN', $data);
        if (!$this->verifySign(compact('notify_data', 'sign'))) {
            return $this->error('验签失败', -1);
        }
        $notifyData = json_decode($notify_data, true);
        if ($this->isSuccess($notifyData)) {
            $merchantTradeNo = $notifyData['ret_data']['buss_seq_no'] ?? '';
            $transaction_id = '';
            return $this->success(array_merge($notifyData, compact('merchantTradeNo', 'transaction_id')));
        } else {
            return $this->error($res['ret_msg'] ?? '系统异常', $res['ret_code'] ?? -1);
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
     * @throws \Exception
     */
    function sign($data): string
    {
        if (!openssl_sign($data, $encryptStr, $this->getPrivateKey(), OPENSSL_ALGO_SHA256)) {
            throw new \Exception('加签失败');
        }
        return base64_encode($encryptStr);
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        $content = $data['notify_data'];
        $sign = $data['sign'];
        if (!$content || $content === 'null') {
            $content = '平台签发';
        }
        return openssl_verify($content, base64_decode($sign), $this->getPublicKey(), OPENSSL_ALGO_SHA256) === 1;
    }

    private function getPrivateKey()
    {
        return openssl_get_privatekey("-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($this->config->privateSecretLtc, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----");
    }

    private function getPublicKey()
    {
        return openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . wordwrap($this->config->publicSecretLtc, 64, "\n", true) . "\n-----END PUBLIC KEY-----");
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    private function execRequest($params, $url) {
        $commonParams = json_encode(array_filter(array_merge([
            "pay_code" => $this->config->appKeyLtc,
            "version" => 'V3.0.0'
        ], $params, $this->config->optional)));

        $client = new Client([
            'base_uri' => $this->config->requestDomainLtc,
            'timeout' => $this->config->requestTimeout ?? 10
        ]);
        $response = $client->post($url, [
            'json' => [
                'data' => $commonParams,
                'sign' => $this->sign($commonParams)
            ]
        ]);
        $responseData = $response->getBody()->getContents();
        return json_decode($responseData, true);
    }

    private function isSuccess($data): bool
    {
        return isset($data['ret_code']) && $data['ret_code'] === '0000';
    }

    private function isPaying($data): bool
    {
        return isset($data['ret_code']) && $data['ret_code'] === '9008';
    }
}
