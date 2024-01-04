<?php

namespace Yijin\Pay\AbroadPayment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Response;

class NetsPay extends Base
{
    use Response;
    const ORDER_REQUEST = '/qr/dynamic/v1/order/request';
    const ORDER_QUERY = '/qr/dynamic/v1/transaction/query';
    const ORDER_REVERSAL = '/qr/dynamic/v1/transaction/reversal';
    function terminalPay(): array
    {
        return $this->error('暂不支持', -1);
    }

    function barcodePay(): array
    {
        return $this->error('暂不支持', -1);
    }

    function qrcodePay(): array
    {
        $amount = str_pad(intval($this->config->totalAmount * 100), 12, '0', STR_PAD_LEFT);
        $params = [
            'mti' => '0200',
            'processing_code' => '990000',
            'amount' => $amount,
            'stan' => $this->config->netsSTAN,
            'transaction_time' => date('His'),
            'transaction_date' => date('md'),
            'entry_mode' => '000',
            'condition_code' => '85',
            'institution_code' => '20000000001',
            'host_tid' => $this->config->netsTID,
            'host_mid' => $this->config->netsMID,
            'npx_data' => [
                'E103' => $this->config->netsTID,
                'E201' => $amount,
                'E202' => 'SGD',
            ],
            'communication_data' => [
                'type' => 'http_proxy',
                'category' => 'URL',
                'destination' => $this->config->notifyUrl,
            ],
            'getQRCode' => 'Y',
        ];
        list($result, $response) = $this->request(self::ORDER_REQUEST, $params);
        if ($result) {
            return $this->success(array_merge(['payUrl' => $response['qr_code']], $response));
        } else {
            return $this->error($response, -1);
        }
    }

    function webPay(): array
    {
        return $this->error('暂不支持', -1);
    }

    function query(): array
    {
        // TODO: Implement query() method.
    }

    function refund(): array
    {
        // TODO: Implement refund() method.
    }

    function refundQuery(): array
    {
        // TODO: Implement refundQuery() method.
    }

    function notify($data)
    {
        // TODO: Implement notify() method.
    }

    function notifySuccess(array $params = [])
    {
        // TODO: Implement notifySuccess() method.
    }

    function sign($data): string
    {
        $encryptStr = json_encode($data) . $this->config->netsKey;
        return base64_encode(hash('sha256', $encryptStr, true));
    }

    function verifySign(array $data): bool
    {
        return false;
    }

    private function request(string $uri, array $params): array
    {
        $client = new Client([
            'base_uri' => $this->config->isSandbox ? 'https://uat-api.nets.com.sg' : 'https://api.nets.com.sg',
            'timeout' => 60,
        ]);
        try {
            $headers = [
                'Sign' => $this->sign($params),
                'KeyId' => $this->config->netsKeyId,
            ];
            echo json_encode($params) . PHP_EOL;
            echo json_encode($headers) . PHP_EOL;
            $response = $client->post(($this->config->isSandbox ? 'uat/merchantservices' : 'merchantservices' ) .$uri, [
                'headers' => $headers,
                'json' => $params
            ]);
            $res = json_decode($response->getBody()->getContents(), true);
            if (isset($res['response_code']) && $res['response_code'] === '00') {
                return [true, $res];
            } else {
                return [false, json_encode($res)];
            }
        } catch (GuzzleException $e) {
            return [false, $e->getMessage()];
        }
    }
}
