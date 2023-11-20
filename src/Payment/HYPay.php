<?php
declare(strict_types=1);

namespace Yijin\Pay\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

class HYPay extends Base
{
    use Response;

    const PAY_URL = 'core/pay/ask-for.do';
    const QUERY_URL = 'core/api/order';
    const REFUND_URL = 'admin/api/refundService.html';
    const REFUND_QUERY_URL = 'admin/api/refund/query';

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        $params = [
            'AccountType' => 1,
            'AccountCode' => $this->config->merchantCodeHY,
            'OrderId' => $this->config->tradeNo,
            'MerchantId' => $this->config->merchantIdHY,
            'ProductId' => $this->config->productIdHY,
            'ProCount' => 1,
            'PayAmount' => $this->config->totalAmount,
            'AuthCode' => $this->config->authCode,
            'PayNotifyPageURL' => $this->config->notifyUrl,
            'PayType' => 98,
            'ThirdChannel' => $this->getChannel(),
        ];
        list($result, $data) = $this->xmlRequest(self::PAY_URL, $params);
        if ($result && isset($data['BusiData'])) {
            $status = $data['BusiData']['Status'] ?? '';
            switch ($status) {
                case 0:
                    $trade_status = Config::PAY_SUCCESS;
                    break;
                case 2:
                    $trade_status = Config::PAYING;
                    break;
                default:
                    $trade_status = Config::PAY_FAIL;
            }
            return $this->success(compact('trade_status', 'data'));
        } else {
            return $this->error($data['return_msg'] ?? 'pay error', -1);
        }
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        return $this->error('暂不支持此支付方式', -1);
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        return $this->error('暂不支持此支付方式', -1);
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        $params = [
            'origin_id' => $this->config->originIdHY,
            'order_id' => $this->config->tradeNo,
        ];
        $params['verify_code'] = $this->sign($params, 'UTF-8');
        list($result, $data) = $this->jsonRequest(self::QUERY_URL, $params);
        if ($result) {
            $status = $data['status'] ?? '';
            switch ($status) {
                case 0:
                    $trade_status = Config::PAY_SUCCESS;
                    break;
                case 2:
                    $trade_status = Config::PAYING;
                    break;
                default:
                    $trade_status = Config::PAY_FAIL;
            }
            return $this->success(compact('trade_status', 'data'));
        } else {
            return $this->error($data, -1);
        }
    }

    /**
     * @inheritDoc
     */
    function refund()
    {
        $params = [
            'TradeNO' => $this->config->tradeNo,
            'RefundOrderId' => $this->config->refundTradeNo,
            'RefundNotifyURL' => $this->config->notifyUrl,
            'RefundReason' => '商户退款',
            'RefundAmount' => $this->config->totalAmount,
        ];
        list($result, $data) = $this->xmlRequest(self::REFUND_URL, $params);
        if ($result && isset($data['BusiData'])) {
            $status = intval($data['BusiData']['ReturnCode'] ?? '-1');
            switch ($status) {
                case 200:
                    $refund_status = Config::REFUND_SUCCESS;
                    break;
                case 202:
                    $refund_status = Config::REFUNDING;
                    break;
                default:
                    $refund_status = Config::REFUND_FAIL;
            }
            return $this->success(compact('refund_status', 'data'));
        } else {
            return $this->error($data['return_msg'] ?? 'pay error', -1);
        }
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        $params = [
            'origin_id' => $this->config->originIdHY,
            'trade_no' => $this->config->tradeNo,
        ];
        $params['verify_code'] = $this->sign($params, 'UTF-8');
        list($result, $data) = $this->jsonRequest(self::REFUND_QUERY_URL, $params);
        if ($result) {
            $status = $data['status'] ?? '';
            switch ($status) {
                case 0:
                    $refund_status = Config::REFUND_SUCCESS;
                    break;
                case 2:
                    $refund_status = Config::REFUNDING;
                    break;
                default:
                    $refund_status = Config::REFUND_FAIL;
            }
            return $this->success(compact('refund_status', 'data'));
        } else {
            return $this->error($data, -1);
        }
    }

    /**
     * @inheritDoc
     */
    function notify($data)
    {
        $type = $data['type'] ?? Config::HY_PAY_NOTIFY;
        if ($type === Config::HY_REFUND_NOTIFY) {
            $xmlData = $data['xml'] ?? [];
            if (!$this->verifySign($xmlData)) {
                return $this->error('验签失败', -1);
            }
            $status = intval($xmlData['BusiData']['ReturnCode'] ?? 201);
            if ($status === 200) {
                $merchantTradeNo = $xmlData['BusiData']['TradeNO'] ?? '';
                return $this->success(array_merge($xmlData, compact('merchantTradeNo')));
            } else {
                return $this->error($xmlData['BusiData']['ReturnMsg'] ?? '', -1);
            }
        } else {
            $xmlData = $this->xmlToArray($data['xml'] ?? '');
            if (!$this->verifySign($xmlData)) {
                return $this->error('验签失败', -1);
            }
            $status = intval($xmlData['BusiData']['Status'] ?? 1);
            if ($status === 0) {
                $merchantTradeNo = $xmlData['BusiData']['OrderId'] ?? '';
                return $this->success(array_merge($xmlData, compact('merchantTradeNo')));
            } else {
                return $this->error($xmlData['BusiData']['StatusInfo'] ?? '', -1);
            }
        }
    }

    /**
     * @inheritDoc
     */
    function notifySuccess()
    {
        return 'SUCCESS';
    }

    /**
     * @inheritDoc
     */
    function sign($data, $charset = 'GBK'): string
    {
        $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->hexToStr($this->config->privateKeyHY), 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";

        $key = openssl_get_privatekey($privateKey);
        openssl_sign($this->generateSignStr($data), $signature, $key);
        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($key);
        }
        return bin2hex($signature);
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        $pubInfo = $data['PubInfo'] ?? [];
        $businessData = $data['BusiData'] ?? [];
        $sign = $pubInfo['VerifyCode'] ?? '';
        if (!$sign) {
            return false;
        }

        $pubKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->hexToStr($this->config->publicKeyHY), 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";

        $key = openssl_pkey_get_public($pubKey);
        return openssl_verify($this->generateSignStr(array_merge($pubInfo, $businessData)), hex2bin($sign), $key) === 1;
    }

    private function generateSignStr(array $data, $charset = 'GBK'): string
    {
        ksort($data);
        $str = [];
        foreach ($data as $key => $val) {
            if ($val !== '' && $val !== null && $key !== 'DigestAlg' && $key !== 'VerifyCode') {
                $str[] = $key . '=' . $val;
            }
        }
        $res = implode('&', $str);
        return $charset === 'GBK' ? mb_convert_encoding($res, 'GBK') : $res;
    }

    private function xmlRequest(string $uri, array $params): array
    {
        $client = new Client([
            'base_uri' => $this->config->domainHY,
            'timeout' => 10,
        ]);
        try {
            $xml = $this->buildXML($params);
            $response = $client->get($uri . '?xml=' . $xml);
            return [true, $this->xmlToArray($response->getBody()->getContents())];
        } catch (GuzzleException $e) {
            return [false, ['return_msg' => $e->getMessage()]];
        }
    }

    private function jsonRequest(string $uri, array $params): array
    {
        $client = new Client([
            'base_uri' => $this->config->domainHY,
            'timeout' => 10,
        ]);
        try {
            $response = $client->post($uri, ['json' => $params]);
            $res = $response->getBody()->getContents();
            $data = json_decode($res, true);
            if (isset($data['return_code']) && (int)$data['return_code'] === 0) {
                return [true, $data['data']];
            } else {
                return [false, $data['return_msg'] ?? 'pay error'];
            }
        } catch (GuzzleException $e) {
            return [false, $e->getMessage()];
        }
    }

    private function buildXML(array $params): string
    {
        $xml = "<AdvPay><PubInfo>";
        $commonParams = [
            'Version' => '1.00',
            'TransactionId' => uniqid(),
            'TransactionDate' => date('YmdHis'),
            'OriginId' => $this->config->originIdHY,
            'DigestAlg' => 'RSA'
        ];
        $commonParams['VerifyCode'] = $this->sign(array_merge($commonParams, $params));
        foreach ($commonParams as $key => $val) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        }
        $xml .= "</PubInfo><BusiData>";
        foreach ($params as $key => $val) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        }
        $xml .= "</BusiData></AdvPay>";
        return $xml;
    }

    private function xmlToArray(string $xml): array
    {
        try {
            $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            return json_decode(json_encode($xml), true);
        } catch (\Throwable $e) {
            return json_decode($xml, true);
        }
    }

    private function hexToStr(string $hex): string
    {
        return base64_encode(hex2bin($hex));
    }

    private function getChannel(): string
    {
        switch ($this->config->payType) {
            case Config::ALIPAY:
                return 'alipay';
            case Config::WE_PAY:
                return 'wechat';
            case Config::YSF_PAY:
                return 'unionpay';
            default:
                return '';

        }
    }
}
