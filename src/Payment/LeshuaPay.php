<?php

namespace Yijin\Pay\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

class LeshuaPay extends Base
{
    use Response;
    // B扫C
    const BARCODE_PAY_URL = 'upload_authcode';
    // C扫B
    const QRCODE_PAY_URL = 'get_tdcode';
    // H5/小程序支付
    const WEB_PAY_URL = 'get_tdcode';
    // 订单支付结果查询
    const ORDER_QUERY_URL = 'query_status';
    // 退款接口
    const REFUND_URL = 'unified_refund';
    // 退款查询接口
    const REFUND_QUERY_URL = 'unified_query_refund';

    /**
     * @var string $domain 接口域名
     */
    public $domain = 'https://paygate.leshuazf.com/cgi-bin/lepos_pay_gateway.cgi';

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        if (!$this->checkTradeNo($this->config->tradeNo)) {
            return $this->error('乐刷的订单号只能包含大小写字母和数字', -1);
        }
        $params = [
            //业务参数
            "third_order_id" => $this->config->tradeNo,
            "amount" => intval($this->config->totalAmount * 100),
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
            $status = $res['status'] ?? '';
            switch ($status) {
                case 2:
                    $trade_status = Config::PAY_SUCCESS;
                    break;
                case 0:
                    $trade_status = Config::PAYING;
                    break;
                default:
                    $trade_status = Config::PAY_FAIL;
            }
            return $this->success(array_merge($res, compact('trade_status')));
        } else {
            return $this->error($res['error_msg'] ?? ($res['resp_msg'] ?? '系统异常'), $res['error_code'] ?? ($res['result_code'] ?? -1));
        }
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        if (!$this->checkTradeNo($this->config->tradeNo)) {
            return $this->error('乐刷的订单号只能包含大小写字母和数字', -1);
        }
        $params = [
            "pay_way" => $this->getPayWay(),
            "third_order_id" => $this->config->tradeNo,
            "amount" => intval($this->config->totalAmount * 100),
            "jspay_flag" => $this->config->payType === Config::WE_PAY ? '2' : '0',
            "jump_url" => urlencode($this->config->jumpUrlLS),
        ];
        try {
            $res = $this->execRequest($params, self::QRCODE_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $payUrl = $res['jspay_url'] ?? ($res['td_code'] ?? '');
            return $this->success(array_merge($res, compact('payUrl')));
        } else {
            return $this->error($res['error_msg'] ?? '系统异常', $res['error_code'] ?? ($res['result_code'] ?? -1));
        }
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        if (!$this->checkTradeNo($this->config->tradeNo)) {
            return $this->error('乐刷的订单号只能包含大小写字母和数字', -1);
        }
        $params = [
            "pay_way" => $this->getPayWay(),
            "third_order_id" => $this->config->tradeNo,
            "amount" => intval($this->config->totalAmount * 100),
            "appid" => $this->config->appid,
            "sub_openid" => $this->config->userId,
            "jspay_flag" => $this->config->isMiniProgram ? 3 : 1,
            "notify_url" => urlencode($this->config->notifyUrl),
        ];
        try {
            $res = $this->execRequest($params, self::WEB_PAY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $info = $res['jspay_info'] ? json_decode($res['jspay_info'], true) : [];
            if ($this->config->payType === Config::WE_PAY) {
                $data['jsApiParameters'] = $info;
            } else {
                $data['trade_no'] = $info['tradeNO'] ?? '';
            }
            return $this->success(array_merge($res, $data));
        } else {
            return $this->error($res['error_msg'] ?? '系统异常', $res['error_code'] ?? ($res['result_code'] ?? -1));
        }
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        $params = [
            "third_order_id" => $this->config->tradeNo,
        ];

        try {
            $res = $this->execRequest($params, self::ORDER_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $status = $res['status'] ?? '';
            switch ($status) {
                case 2:
                    $trade_status = Config::PAY_SUCCESS;
                    break;
                case 0:
                    $trade_status = Config::PAYING;
                    break;
                default:
                    $trade_status = Config::PAY_FAIL;
            }
            return $this->success(array_merge($res, compact('trade_status')));
        } else {
            return $this->error($res['error_msg'] ?? ($res['resp_msg'] ?? '系统异常'), $res['error_code'] ?? ($res['result_code'] ?? -1));
        }
    }

    /**
     * @inheritDoc
     */
    function refund()
    {
        if (!$this->checkTradeNo($this->config->tradeNo) && !$this->checkTradeNo($this->config->refundTradeNo)) {
            return $this->error('乐刷的订单号只能包含大小写字母和数字', -1);
        }
        $params = [
            "third_order_id" => $this->config->tradeNo,
            "merchant_refund_id" => $this->config->refundTradeNo,
            "refund_amount" => intval($this->config->totalAmount * 100),
            "notify_url" => $this->config->notifyUrl ? urlencode($this->config->notifyUrl) : '',
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $status = $res['status'] ?? '';
            switch ($status) {
                case 11:
                    $refund_status = Config::REFUND_SUCCESS;
                    break;
                case 10:
                    $refund_status = Config::REFUNDING;
                    break;
                default:
                    $refund_status = Config::REFUND_FAIL;
            }
            return $this->success(array_merge($res, compact('refund_status')));
        } else {
            return $this->error($res['error_msg'] ?? ($res['resp_msg'] ?? '系统异常'), $res['error_code'] ?? ($res['result_code'] ?? -1));
        }
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        $params = [
            "third_order_id" => $this->config->tradeNo,
            "merchant_refund_id" => $this->config->refundTradeNo,
        ];
        try {
            $res = $this->execRequest($params, self::REFUND_QUERY_URL);
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
        if ($this->isSuccess($res)) {
            $status = $res['status'] ?? '';
            switch ($status) {
                case 11:
                    $refund_status = Config::REFUND_SUCCESS;
                    break;
                case 10:
                    $refund_status = Config::REFUNDING;
                    break;
                default:
                    $refund_status = Config::REFUND_FAIL;
            }
            return $this->success(array_merge($res, compact('refund_status')));
        } else {
            return $this->error($res['error_msg'] ?? ($res['resp_msg'] ?? '系统异常'), $res['error_code'] ?? ($res['result_code'] ?? -1));
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
            $status = $data['status'] ?? '';
            if ($status === 2 || $status === 11) {
                $merchantTradeNo = $data['third_order_id'] ?? '';
                return $this->success(array_merge($data, compact('merchantTradeNo')));
            }
        }
        return $this->error($res['failure_reason'] ?? '系统异常', $res['error_code'] ?? -1);
    }

    /**
     * @inheritDoc
     */
    function notifySuccess()
    {
        return '000000';
    }

    /**
     * @inheritDoc
     */
    function sign($data): string
    {
        ksort($data);
        $signString = "";
        foreach ($data as $key => $val) {
            if ($key === 'sign' || $key === 'leshua' || $key === 'error_code' || empty($val)) {
                continue;
            }
            $signString .= $key . "=" . $val . "&";
        }
        $signString .= 'key=' .  $this->config->serviceProviderKeyLS;
        return strtoupper(md5($signString));
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
    private function execRequest($params, $service): array
    {
        $commonParams = array_merge([
            "merchant_id" => $this->config->merchantIdLS,
            "nonce_str" => md5(time()),
            "service" => $service,
        ], $params, $this->config->optional);
        $commonParams['sign'] = $this->sign($commonParams);

        $client = new Client([
            'timeout' => $this->config->requestTimeout ?? 10
        ]);
        $response = $client->post($this->domain, [
            'form_params' => $commonParams,
        ]);
        $responseData = $response->getBody()->getContents();
        return $this->xml2Array($responseData);
    }

    private function isSuccess($data): bool
    {
        return isset($data['resp_code']) && $data['resp_code'] == '0' && isset($data['result_code']) && $data['result_code'] == '0';
    }

    private function checkTradeNo($tradeNo): bool
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $tradeNo) > 0;
    }

    private function xml2Array($xml): array
    {
        try {
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader();
            }
            $xml = str_replace('&','&amp;', $xml);
            $res = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } catch (\Exception $e) {
            $res = ['result_code' => '1', 'error_msg' => 'xml解析失败，' . $e->getMessage()];
        }
        return $res ?: ['result_code' => '1', 'error_msg' => 'xml解析失败'];
    }

    private function getPayWay(): string
    {
        switch ($this->config->payType) {
            case Config::WE_PAY:
                return 'WXZF';
            case Config::YSF_PAY:
                return 'UPSMZF';
            default:
                return 'ZFBZF';
        }
    }
}
