<?php

namespace Yijin\Pay\AbroadPayment;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\AbroadConfig;
use Yijin\Pay\Response;

class MangoPay extends Base
{
    use Response;

    const DOMAIN_UAT = 'http://122.8.181.200:50001';
    const DOMAIN = 'http://XXXX';
    const ORDER_PAY_URL = '/global/api/paycreatebill';
    const ORDER_QUERY_URL = '/global/api/orderQuery';

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
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function qrcodePay(): array
    {
        $params = [
            'version' => '1.0',
            'cashierLanguage' => $this->config->isSandbox ? 'zh_cn' : 'en_us',
            'orderNum' => $this->config->tradeNo,
            'amount' => (string)round($this->config->totalAmount, 2),
            'currency' => 'MXN',
            'notifyUrl' => $this->config->notifyUrl,
            'callbackUrl' => ''
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

        $data = $res['returnData'] ?? [];
        $payUrl = $data['cashierUrl'] ?? '';
        return $this->success(compact('payUrl'));
    }

    /**
     * @inheritDoc
     */
    function webPay(): array
    {
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function query(): array
    {
        $params = [
            'version' => '1.0',
            'orderNum' => $this->config->tradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

        $data = $res['returnData'] ?? [];
        $code = intval($data['returnCode'] ?? '');
        switch ($code) {
            case 30000:
                $trade_status = AbroadConfig::PAY_SUCCESS;
                break;
            case 30001:
                $trade_status = AbroadConfig::PAY_FAIL;
                break;
            default:
                $trade_status = AbroadConfig::PAYING;
        }
        return $this->success(array_merge($data, compact('trade_status')));
    }

    /**
     * @inheritDoc
     */
    function refund(): array
    {
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function refundQuery(): array
    {
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function notify($data): array
    {
        try {
            $sign = $data[0];
            $postData = $data[1];
            if (!$sign || !isset($postData['encryData'])) {
                return $this->error('参数错误', -1);
            }
            $responseDataDecrypt = json_decode($this->decrypt($postData['encryData']), true);

            if (!$this->verifySign([
                'params' => $responseDataDecrypt,
                'sign' => $sign
            ])) {
                return $this->error('验签失败', -1);
            }
            $merchantTradeNo = $responseDataDecrypt['orderNum'] ?? '';
            $transaction_id = $responseDataDecrypt['txnNo'] ?? '';
            $code = intval($responseDataDecrypt['returnCode'] ?? '');
            switch ($code) {
                case 30000:
                    return $this->success(array_merge($responseDataDecrypt, compact('merchantTradeNo', 'transaction_id')));
                default:
                    return $this->error($responseDataDecrypt['returnMsg'] ?? '系统异常', $code);
            }
        } catch (Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
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
        ksort($data);
        $str = [];
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            $str[] = $key . '=' . $value;
        }
        $str[] = 'key' . '=' . $this->config->mangoPlatformSalt;
        return md5(implode('&', $str));
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        $params = $data['params'];
        ksort($params);
        $str = [];
        foreach ($params as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            $str[] = $key . '=' . (is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES));
        }
        $str[] = 'key' . '=' . $this->config->mangoMerchantSalt;
        return md5(implode('&', $str)) === $data['sign'];
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function execRequest($params, $url) {
        $sign = $this->sign($params);

        $client = new Client([
            'base_uri' => $this->config->isSandbox ? self::DOMAIN_UAT : self::DOMAIN,
            'timeout' => 10,
            'http_errors'=> false
        ]);
        $response = $client->post($url, [
            'json' => [
                'encryData' => [$this->encrypt(json_encode($params, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES))]
            ],
            'headers' => [
                'country' => 'MX',
                'mchtId' => $this->config->mangoMerchantNo,
                'sign' => $sign,
            ],
        ]);
        $responseData = $response->getBody()->getContents();
        $data = json_decode($responseData, true);
        if (isset($data['encryData'])) {
            $responseDataDecrypt = json_decode($this->decrypt($data['encryData']), true);
            if (!$this->isSuccess($responseDataDecrypt)) {
                throw new Exception($responseDataDecrypt['returnMsg'] ?? '系统异常', $responseDataDecrypt['returnCode'] ?? -1);
            }
            if (!$this->verifySign([
                'params' => $responseDataDecrypt,
                'sign' => $response->getHeaderLine('sign')
            ])) {
                throw new Exception('验签失败', -1);
            }
            return $responseDataDecrypt;
        } else {
            throw new Exception($data['returnMsg'] ?? '系统异常', $data['returnCode'] ?? -1);
        }
    }

    private function encrypt(string $data): string
    {
        $publicKey = openssl_pkey_get_public("-----BEGIN PUBLIC KEY-----\n" . wordwrap($this->config->mangoPlatformKey, 64, "\n", true) . "\n-----END PUBLIC KEY-----");
        $parts = str_split($data, 245); // 245 bytes is the maximum for RSA 2048 with PKCS1Padding

        $encrypted = '';
        foreach($parts as $part){
            $encryptedPart = '';
            openssl_public_encrypt($part, $encryptedPart, $publicKey);
            $encrypted .= base64_encode($encryptedPart);
        }
        return $encrypted;
    }
    private function decrypt(array $data): string
    {
        $privateKey = openssl_pkey_get_private("-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($this->config->mangoMerchantKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----");
        $decrypted = '';
        foreach ($data as $part) {
            $decryptedPart = '';
            openssl_private_decrypt(base64_decode($part), $decryptedPart, $privateKey);
            $decrypted .= $decryptedPart;
        }
        return $decrypted;
    }

    private function isSuccess($data): bool
    {
        return isset($data['returnCode']) && $data['returnCode'] == '30000';
    }
}
