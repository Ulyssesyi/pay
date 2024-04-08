<?php

namespace Yijin\Pay\AbroadPayment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\AbroadConfig;
use Yijin\Pay\Response;

class NetsPay extends Base
{
    use Response;
    const ORDER_REQUEST = '/qr/dynamic/v1/order/request';
    const ORDER_QUERY = '/qr/dynamic/v1/transaction/query';
    const ORDER_REVERSAL = '/qr/dynamic/v1/transaction/reversal';

    private $NPSCodeMap = [
        '00' => [
            'result' => true,
            'msg' => 'Approved or completed successfully',
        ],
        '01' => [
            'result' => false,
            'msg' => 'System is under Maintenance',
        ],
        '03' => [
            'result' => false,
            'msg' => 'Invalid Expiry date/Invalid institution code',
        ],
        '05' => [
            'result' => false,
            'msg' => 'Do not honour',
        ],
        '06' => [
            'result' => false,
            'msg' => 'Error',
        ],
        '09' => [
            'result' => true,
            'msg' => 'Request in progress',
        ],
        '12' => [
            'result' => false,
            'msg' => 'Invalid transaction',
        ],
        '13' => [
            'result' => false,
            'msg' => 'Invalid amount',
        ],
        '15' => [
            'result' => false,
            'msg' => 'SOF not found',
        ],
        '30' => [
            'result' => false,
            'msg' => 'Message Format Error',
        ],
        '55' => [
            'result' => false,
            'msg' => 'Invalid PIN',
        ],
        '58' => [
            'result' => false,
            'msg' => 'SOF not enabled for the terminal/Schema Payload not found',
        ],
        '63' => [
            'result' => false,
            'msg' => 'Invalid Signature',
        ],
        '68' => [
            'result' => false,
            'msg' => 'Transaction timed out',
        ],
        '76' => [
            'result' => false,
            'msg' => 'Transaction not found',
        ],
        '92' => [
            'result' => false,
            'msg' => 'No route found to bank',
        ],
        '94' => [
            'result' => false,
            'msg' => 'Order already exists',
        ],
        '96' => [
            'result' => false,
            'msg' => 'Invalid order state',
        ],
        '99' => [
            'result' => false,
            'msg' => 'System Error',
        ],
        'U9' => [
            'result' => false,
            'msg' => 'Pin Required',
        ],
        'ZZ' => [
            'result' => false,
            'msg' => 'Transaction Not Supported',
        ]
    ];
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
        $amount = str_pad(intval(bcmul($this->config->totalAmount, 100)), 12, '0', STR_PAD_LEFT);
        $params = [
            'mti' => '0200',
            'process_code' => '990000',
            'amount' => $amount,
            'stan' => $this->config->netsSTAN,
            'transaction_time' => date('his'),
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
                [
                    'type' => 'https_proxy',
                    'category' => 'URL',
                    'destination' => $this->config->notifyUrl,
                ]
            ],
            'getQRCode' => 'Y',
        ];
        list($result, $response) = $this->request(self::ORDER_REQUEST, $params);
        if ($result) {
            $payUrl = $response['qr_code'] ?? '';
            return $this->success(array_merge($response, compact('payUrl')));
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
        $amount = str_pad(intval(bcmul($this->config->totalAmount, 100)), 12, '0', STR_PAD_LEFT);
        $params = [
            'mti' => '0100',
            'process_code' => '330000',
            'stan' => $this->config->netsSTAN,
            'transaction_time' => date('his'),
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
            'txn_identifier' => $this->config->netsTxnIdentifier
        ];
        list($result, $response) = $this->request(self::ORDER_REQUEST, $params);
        if ($result) {
            switch ($response['response_code']) {
                case '00':
                    $trade_status = AbroadConfig::PAY_SUCCESS;
                    break;
                case '09':
                    $trade_status = AbroadConfig::PAYING;
                    break;
                default:
                    $trade_status = AbroadConfig::PAY_FAIL;
                    $this->reversal();
            }
            $transaction_id = $this->config->netsTxnIdentifier;
            return $this->success(array_merge($response, compact('trade_status', 'transaction_id')));
        } else {
            $this->reversal();
            return $this->error($response, -1);
        }
    }

    function refund(): array
    {
        return $this->error('暂不支持', -1);
    }

    function refundQuery(): array
    {
        return $this->error('暂不支持', -1);
    }

    function notify($data): array
    {
        $merchantTradeNo = $data['stan'] ?? '';
        $transaction_id = $data['txn_identifier'] ?? '';
        return $this->success(array_merge($data, compact('merchantTradeNo', 'transaction_id')));
    }

    function notifySuccess(array $params = []): string
    {
        return 'success';
    }

    function sign($data): string
    {
        $encryptStr = json_encode($data) . $this->config->netsKey;
        return base64_encode(hash('sha256', $encryptStr, true));
    }

    function verifySign(array $data): bool
    {
        return true;
    }

    private function reversal(): bool
    {
        $amount = str_pad(intval(bcmul($this->config->totalAmount, 100)), 12, '0', STR_PAD_LEFT);
        $params = [
            'mti' => '0400',
            'process_code' => '990000',
            'stan' => $this->config->netsSTAN,
            'amount' => $amount,
            'transaction_time' => date('his'),
            'transaction_date' => date('md'),
            'entry_mode' => '000',
            'condition_code' => '85',
            'institution_code' => '20000000001',
            'host_tid' => $this->config->netsTID,
            'host_mid' => $this->config->netsMID,
            'npx_data' => [
                'E103' => $this->config->netsTID,
            ],
            'txn_identifier' => $this->config->netsTxnIdentifier,
        ];
        list($result, $response) = $this->request(self::ORDER_REVERSAL, $params);
        if ($result) {
            return $response['response_code'] == '00';
        } else {
            return false;
        }
    }

    private function request(string $uri, array $params): array
    {
        $client = new Client([
            'base_uri' => $this->config->isSandbox ? 'https://uat-api.nets.com.sg:9065' : 'https://api.nets.com.sg',
            'timeout' => 60,
        ]);
        try {
            $response = $client->post(($this->config->isSandbox ? 'uat/merchantservices' : 'merchantservices' ) .$uri, [
                'headers' => [
                    'Sign' => $this->sign($params),
                    'Keyid' => $this->config->netsKeyId,
                ],
                'json' => $params
            ]);
            $res = json_decode($response->getBody()->getContents(), true);
            if (isset($res['response_code'])) {
                $resultParse = $this->NPSCodeMap[$res['response_code']] ?? [
                    'result' => false,
                    'msg' => 'Unknown Error：' . $res['response_code'],
                ];
                if ($resultParse['result']) {
                    return [true, $res];
                } else {
                    return [false, $resultParse['msg']];
                }
            } else {
                return [false, json_encode($res)];
            }
        } catch (GuzzleException $e) {
            return [false, $e->getMessage()];
        }
    }
}
