<?php
declare(strict_types=1);
namespace Yijin\Pay\AbroadPayment;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\AbroadConfig;
use Yijin\Pay\Response;

class HiPay extends Base
{
    /**
     * 商户的扫码支付地址;
     */
    const ORDER_PAY_URL = '/payment/v1/charge/create';
    /**
     * 商户的支付查询地址;
     */
    const ORDER_QUERY_URL = '/payment/v1/charge/query';

    /**
     * 商户的退款地址;
     */
    const REFUND_URL = '/payment/v1/refund/create';

    /**
     * 商户的退款查询地址;
     */
    const REFUND_QUERY_URL = '/payment/v1/refund/query';
    const DOMAIN = 'https://api.pay.hwipg.com';
    const DOMAIN_UAT = 'https://api.pay-uat.hwipg.com';

    const SING_TYPE = 'SHA256withRSA';
    const API_METHOD_MAP = [
        self::ORDER_PAY_URL => 'ft.charge.create',
        self::ORDER_QUERY_URL => 'ft.charge.query',
        self::REFUND_URL => 'ft.refund.create',
        self::REFUND_QUERY_URL => 'ft.refund.query',
    ];

    use Response;

    /**
     * @inheritDoc
     */
    function terminalPay(): array
    {
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function barcodePay(): array
    {
        $params = [
            'notify_url' => $this->config->notifyUrl,
            'merch_order_id' => $this->config->tradeNo,
            'channel' => 'mm_kbzpay_micropay',
            'amount' => $this->config->totalAmount,
            'currency' => 'MMK',
            'goods_subject' => $this->config->subject ?? 'Merchant Order',
            'goods_body' => '',
            'time_expire' => 15 * 60,
            'channel_extra' => [
                'auth_code' => $this->config->authCode,
                'trans_type' => 'OnlinePaymentISV'
            ],
            'meta_data' => [],
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

        if ($this->isSuccess($res)) {
            $data = $res['biz_content'] ?? [];
            if ($data['paid']) {
                $trade_status = AbroadConfig::PAY_SUCCESS;
            } else {
                $trade_status = AbroadConfig::PAYING;
            }
            $transaction_id = $data['id'] ?? '';
            return $this->success(array_merge($data, compact('trade_status', 'transaction_id')));
        } else {
            return $this->error($res['error_msg'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function qrcodePay(): array
    {
        $params = [
            'notify_url' => $this->config->notifyUrl,
            'merch_order_id' => $this->config->tradeNo,
            'channel' => 'mm_kbzpay_paybyqrcode',
            'amount' => $this->config->totalAmount,
            'currency' => 'MMK',
            'goods_subject' => $this->config->subject ?? 'Merchant Order',
            'goods_body' => '',
            'time_expire' => 15 * 60,
            'channel_extra' => [],
            'meta_data' => [],
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

        if ($this->isSuccess($res)) {
            $data = $res['biz_content'] ?? [];
            $payUrl = $data['credential']['mm_kbzpay_paybyqrcode']['qrCode'] ?? '';
            return $this->success(array_merge($data, compact('payUrl')));
        } else {
            return $this->error($res['error_msg'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function webPay(): array
    {
        $params = [
            'notify_url' => $this->config->notifyUrl,
            'merch_order_id' => $this->config->tradeNo,
            'channel' => 'mm_kbzpay_pwa',
            'amount' => $this->config->totalAmount,
            'currency' => 'MMK',
            'goods_subject' => $this->config->subject ?? 'Merchant Order',
            'goods_body' => '',
            'time_expire' => 15 * 60,
            'channel_extra' => [],
            'meta_data' => [],
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

        if ($this->isSuccess($res)) {
            $data = $res['biz_content'] ?? [];
            $payUrl = ( $this->config->isSandbox ? 'https://static.kbzpay.com/pgw/uat/pwa/#/?' : 'https://wap.kbzpay.com/pgw/pwa/#/') . ($data['credential']['mm_kbzpay_pwa']['rawRequest'] ?? '');
            return $this->success(array_merge($data, compact('payUrl')));
        } else {
            return $this->error($res['error_msg'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function query(): array
    {
        $params = [
            'merch_order_id' => $this->config->tradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

        if ($this->isSuccess($res)) {
            $data = $res['biz_content'] ?? [];
            if ($data['paid']) {
                $trade_status = AbroadConfig::PAY_SUCCESS;
            } else {
                $trade_status = AbroadConfig::PAYING;
            }
            $transaction_id = $data['id'] ?? '';
            return $this->success(array_merge($data, compact('trade_status', 'transaction_id')));
        } else {
            return $this->error($res['error_msg'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function refund(): array
    {
        $params = [
            'merch_order_id' => $this->config->tradeNo,
            'refund_req_no' => $this->config->refundTradeNo,
            'amount' => $this->config->totalAmount,
            'currency' => 'MMK',
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

        if ($this->isSuccess($res)) {
            $data = $res['biz_content'] ?? [];
            if ($data['succeed']) {
                $refund_status = AbroadConfig::REFUND_SUCCESS;
            } elseif (in_array($data['status'],  ['Initial', 'Wait_Process', 'Processing', 'Wait_Pay'])) {
                $refund_status = AbroadConfig::REFUNDING;
            } else {
                $refund_status = AbroadConfig::REFUND_FAIL;
            }
            return $this->success(array_merge($data, compact('refund_status')));
        } else {
            return $this->error($res['error_msg'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function refundQuery(): array
    {
        $params = [
            'merch_order_id' => $this->config->tradeNo,
            'refund_req_no' => $this->config->refundTradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

        if ($this->isSuccess($res)) {
            $data = $res['biz_content'] ?? [];
            if ($data['succeed']) {
                $refund_status = AbroadConfig::REFUND_SUCCESS;
            } elseif (in_array($data['status'],  ['Initial', 'Wait_Process', 'Processing', 'Wait_Pay'])) {
                $refund_status = AbroadConfig::REFUNDING;
            } else {
                $refund_status = AbroadConfig::REFUND_FAIL;
            }
            return $this->success(array_merge($data, compact('refund_status')));
        } else {
            return $this->error($res['error_msg'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function notify($data): array
    {
        list($headers, $body) = $data;
        $sign = $headers['x-ft-sign'] ?? '';
        $appid = $headers['x-ft-appid'] ?? '';
        $timestamp = $headers['x-ft-timestamp'] ?? '';
        $requestId = $headers['x-ft-requestid'] ?? '';

        if (!$this->verifySign([$body, $sign, $timestamp, $requestId, $appid])) {
            return $this->error('验签失败', -1);
        }
        $bodyData = json_decode($body, true);
        $payData = $bodyData['data'] ?? [];
        $event_type = $data['event_type'] ?? '';
        if ($event_type === 'charge.success') {
            $merchantTradeNo = $payData['merch_order_id'] ?? '';
        } else if ($event_type === 'refund.success') {
            $merchantTradeNo = $payData['refund_req_no'] ?? '';
        } else {
            return $this->error("回调错误", -1);
        }
        $transaction_id = $data['id'] ?? '';
        return $this->success(array_merge($data, compact('merchantTradeNo', 'transaction_id')));
    }

    /**
     * @inheritDoc
     */
    function notifySuccess(array $params = []): string
    {
        return 'success';
    }

    /**
     * @inheritDoc
     */
    function sign($data): string
    {
        list($params, $time, $requestId) = $data;
        $str = implode('|', [json_encode($params, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), $this->config->hipayAppId, self::SING_TYPE, $requestId, $time]);
        $priKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->config->hiPayPrivateKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($str, $signed, $priKey, 'sha256');
        return base64_encode($signed);
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        list($content, $sign, $time, $requestId, $appid) = $data;
        $str = implode('|', [$content, $appid, self::SING_TYPE, $requestId, $time]);
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->config->hiPayPublicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        $key = openssl_get_publickey($publicKey);
        return openssl_verify($str, base64_decode($sign), $key,'sha256');
    }

    /**
     * @throws GuzzleException
     */
    private function execRequest($params, $url) {
        $time = intval(microtime(true) * 1000);
        $requestId = uniqid();
        $content = [
            'method' => self::API_METHOD_MAP[$url],
            "nonce_str" => md5((string)$time),
            'version' => '1.0',
            "biz_content" => array_merge($params, $this->config->optional),
        ];
        $sign = $this->sign([$content, $time, $requestId]);

        $client = new Client([
            'base_uri' => $this->config->isSandbox ? self::DOMAIN_UAT : self::DOMAIN,
            'timeout' => 10,
            'http_errors'=> false
        ]);
        $response = $client->post($url, [
            'body' => json_encode($content, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-ft-AppId' => $this->config->hipayAppId,
                'X-ft-SignType' => self::SING_TYPE,
                'X-ft-Sign' => $sign,
                'X-ft-RequestId' => $requestId,
                'X-ft-Timestamp' => $time,
            ],
        ]);
        $responseData = $response->getBody()->getContents();
        return json_decode($responseData, true);
    }

    private function isSuccess($data): bool
    {
        return isset($data['result']) && $data['result'] === 'SUCCESS';
    }
}
