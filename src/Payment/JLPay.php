<?php

namespace Yijin\Pay\Payment;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

class JLPay extends Base
{
    use Response;

    // B扫C
    const BARCODE_PAY_URL = '/api/pay/micropay';
    // C扫B
    const QRCODE_PAY_URL = '/api/pay/qrcodepay';
    // 微信支付URL
    const WECHAT_PAY_URL = '/api/pay/officialpay';
    // 支付宝支付URL
    const ALI_PAY_URL = '/api/pay/waph5pay';
    // 订单支付结果查询
    const ORDER_QUERY_URL = '/api/pay/chnquery';
    // 退款接口
    const REFUND_URL = '/api/pay/refund';

    public string $domain = 'https://qrcode.jlpay.com';
    public string $uatDomain = 'https://qrcode-uat.jlpay.com';

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        $params = [
            'out_trade_no' => $this->config->tradeNo,
            'body' => $this->config->subject,
            'attach' => $this->config->subject,
            'total_fee' => intval($this->config->totalAmount * 100),
            'notify_url' => $this->config->notifyUrl,
            'auth_code' => $this->config->authCode,
            'mch_create_ip' => $this->config->userIP,
        ];
        try {
            $res = $this->execRequest($params, self::BARCODE_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            switch ($res['status']) {
                case '2':
                    $trade_status = Config::PAY_SUCCESS;
                    break;
                case '1':
                    $trade_status = Config::PAYING;
                    break;
                default:
                    $trade_status = Config::PAY_FAIL;
            }
            return $this->success(array_merge($res, compact('trade_status')));
        } else {
            return $this->error($res['ret_msg'] ?? '系统异常', $res['ret_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        $params = [
            'out_trade_no' => $this->config->tradeNo,
            'body' => $this->config->subject,
            'attach' => $this->config->subject,
            'total_fee' => intval($this->config->totalAmount * 100),
            'pay_type' => $this->getPayType(),
            'notify_url' => $this->config->notifyUrl,
            'mch_create_ip' => $this->config->userIP,
        ];
        try {
            $res = $this->execRequest($params, self::QRCODE_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            if ($res['status'] == 1) {
                $payUrl = $res['code_url'];
                return $this->success(array_merge($res, compact('payUrl')));
            }
            return $this->error('生成二维码失败', -2);
        } else {
            return $this->error($res['ret_msg'] ?? '系统异常', $res['ret_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        if ($this->config->payType === Config::WE_PAY) {
            $params = [
                'out_trade_no' => $this->config->tradeNo,
                'body' => $this->config->subject,
                'attach' => $this->config->subject,
                'total_fee' => intval($this->config->totalAmount * 100),
                'pay_type' => $this->getPayType(),
                'open_id' => $this->config->userId,
                'sub_appid' => $this->config->subAppId,
                'notify_url' => $this->config->notifyUrl,
                'mch_create_ip' => $this->config->userIP,
            ];
            $uri = self::WECHAT_PAY_URL;
        } else {
            $params = [
                'out_trade_no' => $this->config->tradeNo,
                'body' => $this->config->subject,
                'attach' => $this->config->subject,
                'total_fee' => intval($this->config->totalAmount * 100),
                'pay_type' => $this->getPayType(),
                'notify_url' => $this->config->notifyUrl,
                'mch_create_ip' => $this->config->userIP,
                'buyer_id' => $this->config->userId,
            ];
            $uri = self::ALI_PAY_URL;
        }
        try {
            $res = $this->execRequest($params, $uri);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            if ($this->config->payType === Config::WE_PAY) {
                $jsApiParameters = json_decode($res['pay_info'], true);
                return $this->success(array_merge($res, compact('jsApiParameters')));
            } else {
                $trade_no = $res['pay_info'];
                return $this->success(array_merge($res, compact('trade_no')));
            }
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
            'out_trade_no' => $this->config->tradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            switch ($res['status']) {
                case '2':
                    $trade_status = Config::PAY_SUCCESS;
                    break;
                case '1':
                    $trade_status = Config::PAYING;
                    break;
                default:
                    $trade_status = Config::PAY_FAIL;
            }
            return $this->success(array_merge($res, compact('trade_status')));
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
            'out_trade_no' => $this->config->refundTradeNo,
            'ori_out_trade_no' => $this->config->tradeNo,
            'total_fee' => intval($this->config->totalAmount * 100),
            'mch_create_ip' => $this->config->userIP,
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            switch ($res['status']) {
                case '2':
                    $refund_status = Config::REFUND_SUCCESS;
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
    function refundQuery()
    {
        $params = [
            'out_trade_no' => $this->config->refundTradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            switch ($res['status']) {
                case '5':
                    $refund_status = Config::REFUND_SUCCESS;
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
        if (!$this->verifySign($data)) {
            return $this->error('验签失败', -1);
        }
        if ($data['status'] == '2') {
            $merchantTradeNo = $data['out_trade_no'] ?? '';
            return $this->success(array_merge($data, compact('merchantTradeNo')));
        } else {
            return $this->error('支付参数异常', -1);
        }
    }

    /**
     * @inheritDoc
     */
    function notifySuccess()
    {
        return ['retCode' => 'success'];
    }

    /**
     * @inheritDoc
     */
    function sign($data): string
    {
        ksort($data);
        $str = json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
        $privateKey = openssl_pkey_get_private("-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($this->config->privateKeyJL, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----");
        openssl_sign($str, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        $sign = $data['sign'];
        unset($data['sign']);
        ksort($data);
        $str = json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES);
        $publicKey = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . wordwrap($this->config->publicKeyJL, 64, "\n", true) . "\n-----END PUBLIC KEY-----");
        return (bool)openssl_verify($str, base64_decode($sign), $publicKey, OPENSSL_ALGO_SHA256);
    }

    /**
     * @throws GuzzleException
     */
    private function execRequest(array $params, string $url)
    {
        $commonParams = array_filter(array_merge([
            'org_code' => $this->config->orgCodeJL,
            'mch_id' => $this->config->merchantIdJL,
            'term_no' => $this->config->termNoJL,
            'nonce_str' => md5(uniqid()),
        ], $params, $this->config->optional));
        $commonParams['sign'] = $this->sign($commonParams);

        $client = new Client([
            'base_uri' => $this->config->isSandboxJL ? $this->uatDomain : $this->domain,
            'timeout' => $this->config->requestTimeout ?? 10,
        ]);
        $response = $client->post($url, [
            'json' => $commonParams
        ]);
        $responseData = $response->getBody()->getContents();
        return json_decode($responseData, true);
    }

    private function isSuccess($data): bool
    {
        return isset($data['ret_code']) && $data['ret_code'] === '00';
    }

    private function getPayType(): string
    {
        switch ($this->config->payType) {
            case Config::ALIPAY:
                return 'alipay';
            case Config::WE_PAY:
                return 'wxpay';
            default:
                return 'unionpay';
        }
    }
}
