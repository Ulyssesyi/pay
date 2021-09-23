<?php

namespace Yijin\Pay\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

class FuBeiPay extends Base
{
    use Response;
    // B扫C
    const BARCODE_PAY_URL = 'openapi.payment.order.swipe';
    // C扫B
    const QRCODE_PAY_URL = 'openapi.payment.order.scan';
    // 订单支付结果查询
    const ORDER_QUERY_URL = 'openapi.payment.order.query';
    // 退款接口
    const REFUND_URL = 'openapi.payment.order.refund';
    // 退款查询接口
    const REFUND_QUERY_URL = 'openapi.payment.order.refund.query';

    /**
     * @var string $domain 接口域名
     */
    public $domain = 'https://shq-api.51fubei.com/gateway';

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        $params = [
            //业务参数
            "merchant_order_sn" => $this->config->tradeNo,
            "total_fee" => $this->config->totalAmount,
            "store_id" => $this->config->storeIdFb,
            "auth_code" => $this->config->authCode
        ];
        try {
            $res = $this->execRequest($params, self::BARCODE_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $status = $res['data']['trade_state'] ?? '';
            switch ($status) {
                case 'SUCCESS':
                    $trade_status = Config::PAY_SUCCESS;
                    break;
                case 'USERPAYING':
                    $trade_status = Config::PAYING;
                    break;
                default:
                    $trade_status = Config::PAY_FAIL;
            }
            return $this->success(array_merge($res, compact('trade_status')));
        } else {
            return $this->error($res['result_message'] ?? '系统异常', $res['sub_code'] ?? ($res['result_code'] ?? '0001'));
        }
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        $params = [
            "type" => $this->config->payType === Config::WE_PAY ? 1 : 2,
            "merchant_order_sn" => $this->config->tradeNo,
            "total_fee" => $this->config->totalAmount,
            "store_id" => $this->config->storeIdFb,
        ];
        try {
            $res = $this->execRequest($params, self::QRCODE_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $payUrl = $res['data']['qr_code'] ?? '';
            return $this->success(array_merge($res, compact('payUrl')));
        } else {
            return $this->error($res['result_message'] ?? '系统异常', $res['sub_code'] ?? ($res['result_code'] ?? '0001'));
        }
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        $params = [
            "merchant_order_sn" => $this->config->tradeNo,
            "store_id" => $this->config->storeIdFb,
            "total_fee" => $this->config->totalAmount,
            "call_back_url" => $this->config->notifyUrl,
        ];
        if ($this->config->payType === Config::WE_PAY) {
            $params['sub_openid'] = $this->config->userId;
            if ($this->config->isMiniProgram) {
                $method = 'openapi.payment.order.mina';
            } else {
                $method = 'openapi.payment.order.h5pay';
                $params['openid'] = $this->config->wxOpenIDFb;
            }
        } else {
            $method = 'openapi.payment.Alipay.H5';
            $params['buyer_id'] = $this->config->userId;
        }
        try {
            $res = $this->execRequest($params, $method);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            if ($this->config->payType === Config::WE_PAY) {
                $data['jsApiParameters'] = $res['data']['sign_params'] ?? [];
            } else {
                $data['trade_no'] = $data['data']['prepay_id'] ?? '';
            }
            return $this->success($data);
        } else {
            return $this->error($res['result_message'] ?? '系统异常', $res['result_code'] ?? '0001');
        }
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        $params = [
            "merchant_order_sn" => $this->config->tradeNo,
        ];

        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $status = $res['data']['trade_state'] ?? '';
            switch ($status) {
                case 'SUCCESS':
                    $trade_status = Config::PAY_SUCCESS;
                    break;
                case 'USERPAYING':
                    $trade_status = Config::PAYING;
                    break;
                default:
                    $trade_status = Config::PAY_FAIL;
            }
            return $this->success(array_merge($res, compact('trade_status')));
        } else {
            return $this->error($res['result_message'] ?? '系统异常', $res['result_code'] ?? '0001');
        }
    }

    /**
     * @inheritDoc
     */
    function refund()
    {
        $params = [
            "merchant_order_sn" => $this->config->tradeNo,
            "merchant_refund_sn" => $this->config->refundTradeNo,
            "refund_money" => $this->config->totalAmount,
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
            return $this->error($res['result_message'] ?? '系统异常', $res['result_code'] ?? '0001');
        }
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        $params = [
            "merchant_order_sn" => $this->config->tradeNo,
            "merchant_refund_sn" => $this->config->refundTradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $status = $res['data']['refund_status'] ?? '';
            switch ($status) {
                case 'REFUND_SUCCESS':
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
            return $this->error($res['result_message'] ?? '系统异常', $res['result_code'] ?? '0001');
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
        if ($data['result_code'] == 200) {
            $orderInfo = json_decode($data['data'], true);
            $merchantTradeNo = $orderInfo['merchant_order_sn'] ?? '';
            return $this->success(array_merge($orderInfo, compact('merchantTradeNo')));
        } else {
            return $this->error($data['result_message'] ?? '异步错误', $data['result_code'] ?? 500);
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
        return strtoupper(md5($this->generateSignString($data) . $this->config->merchantKeyFb));
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        $sign = $data['sign'];
        return $sign === $this->sign($data);
    }

    private function generateSignString($data): string
    {
        ksort($data);
        $signString = "";
        foreach ($data as $key => $val) {
            if (!empty($val) && $key !== 'sign') {
                $signString .= $key . "=" . $val . "&";
            }
        }
        return rtrim($signString, "&");
    }

    /**
     * @throws GuzzleException
     */
    private function execRequest($params, $method) {
        $commonParams = [
            "app_id" => $this->config->merchantIdFb,
            "method" => $method,
            "format" => 'json',
            "sign_method" => "md5",
            "nonce" => md5(time()),
            'biz_content' => json_encode(array_merge($params, [
                'equipment_type' => 120
            ], $this->config->optional))
        ];
        $commonParams['sign'] = $this->sign($commonParams);

        $client = new Client([]);
        $response = $client->post($this->domain, [
            'json' => $commonParams
        ]);
        $responseData = $response->getBody()->getContents();
        return json_decode($responseData, true);
    }

    private function isSuccess($data): bool
    {
        return isset($data['result_code']) && $data['result_code'] == 200;
    }
}
