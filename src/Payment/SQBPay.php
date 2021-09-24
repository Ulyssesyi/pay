<?php

namespace Yijin\Pay\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

class SQBPay extends Base
{
    use Response;
    // B扫C
    const BARCODE_PAY_URL = 'upay/v2/pay';
    // C扫B
    const QRCODE_PAY_URL = 'upay/v2/precreate';
    // 订单支付结果查询
    const ORDER_QUERY_URL = 'upay/v2/query';
    // 退款接口
    const REFUND_URL = 'upay/v2/refund';
    // 激活接口
    const ACTIVATE_URL = 'terminal/activate';
    // 刷新接口
    const CHECKIN_URL = 'terminal/checkin';

    /**
     * @var string $domain 接口域名
     */
    public $domain = 'https://api.shouqianba.com';

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        $params = [
            'terminal_sn' => $this->config->terminalSNSqb,
            'client_sn' => $this->config->tradeNo,
            'total_amount' => (string)intval($this->config->totalAmount * 100),
            'dynamic_id' => $this->config->authCode,
            'subject' => $this->config->subject,
            'operator' => $this->config->operatorSqb
        ];
        try {
            $res = $this->execRequest($params, self::BARCODE_PAY_URL);
        } catch (GuzzleException | \Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $trade_status = $this->tradeStatus($res);
            return $this->success(array_merge($res, compact('trade_status')));
        } else {
            return $this->error($res['error_message'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        $params = [
            'terminal_sn' => $this->config->terminalSNSqb,
            'client_sn' => $this->config->tradeNo,
            'total_amount' => (string)intval($this->config->totalAmount * 100),
            'payway' => $this->config->payType === Config::WE_PAY ? '3' : '2',
            'subject' => $this->config->subject,
            'operator' => $this->config->operatorSqb
        ];
        try {
            $res = $this->execRequest($params, self::QRCODE_PAY_URL);
        } catch (GuzzleException | \Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        $payUrl = $res['biz_response']['data']['qr_code'] ?? '';
        if ($this->isSuccess($res) && $payUrl) {
            return $this->success(array_merge($res, compact('payUrl')));
        } else {
            return $this->error($res['error_message'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        if ($this->config->isMiniProgram) {
            return $this->error('收钱吧暂不支持小程序支付', -1);
        }
        $params = array_merge([
            'terminal_sn' => $this->config->terminalSNSqb,
            'client_sn' => $this->config->tradeNo,
            'total_amount' => (string)intval($this->config->totalAmount * 100),
            'subject' => $this->config->subject,
            'notify_url' => $this->config->notifyUrl,
            'operator' => $this->config->operatorSqb,
            'return_url' => $this->config->returnUrlSqb,
            'reflect' => $this->config->reflectSqb,
        ], $this->config->optional);
        $params = array_filter($params);
        ksort($params);
        $str = '';
        foreach ($params as $key => $param) {
            $str .= $key .'='. $param .'&';
        }
        $params['sign'] = strtoupper(md5($str) . 'key=' . $this->config->terminalKeySqb);
        return $this->success(['payUrl' => "https://qr.shouqianba.com/gateway?" . http_build_query($params)]);
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        $params = [
            'terminal_sn' => $this->config->terminalSNSqb,
            'client_sn' => $this->config->tradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException | \Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $trade_status = $this->tradeStatus($res);
            return $this->success(array_merge($res, compact('trade_status')));
        } else {
            return $this->error($res['error_message'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function refund()
    {
        $params = [
            'terminal_sn' => $this->config->terminalSNSqb,
            'client_sn' => $this->config->tradeNo,
            'refund_request_no' => $this->config->refundTradeNo,
            'refund_amount' => (string)intval($this->config->totalAmount * 100),
            'operator' => $this->config->operatorSqb
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_URL);
        } catch (GuzzleException | \Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $code = $res['biz_response']['result_code'] ?? '';
            $status = $res['biz_response']['data']['order_status'] ?? '';
            if ($code === 'REFUND_SUCCESS' && $status === 'REFUNDED') {
                $refund_status = Config::REFUND_SUCCESS;
            } elseif ($code === 'REFUND_IN_PROGRESS') {
                $refund_status = Config::REFUNDING;
            } else {
                $refund_status = Config::REFUND_FAIL;
            }
            return $this->success(array_merge($res, compact('refund_status')));
        } else {
            return $this->error($res['error_message'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        $params = [
            'terminal_sn' => $this->config->terminalSNSqb,
            'client_sn' => $this->config->tradeNo,
            'refund_request_no' => $this->config->refundTradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException | \Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $status = $res['biz_response']['data']['order_status'] ?? '';
            if ($status === 'REFUNDED') {
                $refund_status = Config::REFUND_SUCCESS;
            } elseif ($status === 'REFUND_INPROGRESS') {
                $refund_status = Config::REFUNDING;
            } else {
                $refund_status = Config::REFUND_FAIL;
            }
            return $this->success(array_merge($res, compact('refund_status')));
        } else {
            return $this->error($res['error_message'] ?? '系统异常', $res['error_code'] ?? -1);
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
        if (isset($data['status']) && $data['status'] === 'SUCCESS' && isset($data['order_status']) && $data['order_status'] === 'PAID') {
            $merchantTradeNo = $data['client_sn'] ?? '';
            return $this->success(array_merge($data, compact('merchantTradeNo')));
        } else {
            return $this->error($data['order_status'] ?? '内容异常', $data['status'] ?? '-1');
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
        return md5(json_encode($data) . $this->config->terminalKeySqb);
    }

    /**
     * 验证签名,暂无
     */
    function verifySign(array $data): bool
    {
        return true;
    }

    /**
     * 刷新密钥
     */
    public function checkIn(): array
    {
        $params = [
            'terminal_sn' => $this->config->terminalSNSqb,
            'device_id' => $this->config->activateDeviceIDSqb,
        ];
        try {
            $res = $this->execRequest($params, self::CHECKIN_URL);
        } catch (GuzzleException | \Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            return $this->success([
                'terminal_key' => $res['biz_response']['terminal_key'] ?? '',
            ]);
        } else {
            return $this->error($res['error_message'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * 激活生成终端账号密码
     */
    public function activate(): array
    {
        $params = [
            'app_id' => $this->config->serviceProviderIDSqb,
            'code' => $this->config->activateCodeSqb,
            'device_id' => $this->config->activateDeviceIDSqb,
        ];
        try {
            $res = $this->execRequest($params, self::ACTIVATE_URL);
        } catch (GuzzleException | \Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            return $this->success([
                'terminal_sn' => $res['biz_response']['terminal_sn'] ?? '',
                'terminal_key' => $res['biz_response']['terminal_key'] ?? '',
            ]);
        } else {
            return $this->error($res['error_message'] ?? '系统异常', $res['error_code'] ?? -1);
        }
    }

    /**
     * @throws GuzzleException
     */
    private function execRequest($params, $url) {
        $params = array_merge($params, $this->config->optional);
        $sign = $this->sign($params);

        $client = new Client([
            'base_uri' => $this->domain,
        ]);
        $response = $client->post($url, [
            'json' => $params,
            'headers' => [
                'Authorization' => $this->config->terminalSNSqb . ' ' . $sign
            ]
        ]);
        $responseData = $response->getBody()->getContents();
        return json_decode($responseData, true);
    }

    private function isSuccess($data): bool
    {
        return isset($data['result_code']) && (int)$data['result_code'] === 200;
    }

    private function tradeStatus($data): int
    {
        $orderStatus = $data['biz_response']['data']['order_status'] ?? '';
        if ($orderStatus === 'PAID') {
            $trade_status = Config::PAY_SUCCESS;
        } elseif (in_array($orderStatus, [
            'PAY_CANCELED',
            'REFUNDED',
            'PARTIAL_REFUNDED',
            'CANCELED',
        ])) {
            $trade_status = Config::PAY_FAIL;
        } else {
            $trade_status = Config::PAYING;
        }
        return  $trade_status;
    }
}
