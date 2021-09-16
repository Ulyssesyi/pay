<?php

namespace Yijin\Pay\Payment;

use Alipay\EasySDK\Kernel\Factory;
use Yijin\Pay\Config;
use Yijin\Pay\Response;

class Alipay extends Base
{
    use Response;
    public function __construct(Config $config)
    {
        parent::__construct($config);

        $options = new \Alipay\EasySDK\Kernel\Config();
        $options->protocol = 'https';
        $options->gatewayHost = 'openapi.alipay.com';
        $options->signType = 'RSA2';

        $options->appId = $config->appid;

        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        $options->merchantPrivateKey = $config->merchantPrivateKey;

        if ($config->alipayPublicKey) {
            //注：如果采用非证书模式，无需赋值三个证书路径，赋值如下的支付宝公钥字符串即可
             $options->alipayPublicKey = $config->alipayPublicKey;
        } else {
            //注：如果采用证书模式，赋值三个证书路径
            $options->alipayCertPath = $config->alipayCertPath;
            $options->alipayRootCertPath = $config->alipayRootCertPath;
            $options->merchantCertPath = $config->merchantCertPath;
        }

        //可设置异步通知接收服务地址（可选）
        $options->notifyUrl = $config->notifyUrl;

        //可设置AES密钥，调用AES加解密相关接口时需要（可选）
        $options->encryptKey = $config->encryptKey;

        Factory::setOptions($options);
    }

    /**
     * @inheritDoc
     */
    function barcodePay()
    {
        try {
            $client = Factory::payment()->faceToFace();
            if ($this->config->appAuthToken) {
                $client->agent($this->config->appAuthToken);
            }
            $res = $client->pay($this->config->subject, $this->config->tradeNo, $this->config->totalAmount, $this->config->authCode);
            return $this->success($res->toMap());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function qrcodePay()
    {
        try {
            $client = Factory::payment()->faceToFace();
            if ($this->config->appAuthToken) {
                $client->agent($this->config->appAuthToken);
            }
            $res = $client->preCreate($this->config->subject, $this->config->tradeNo, $this->config->totalAmount);
            return $this->success($res);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function webPay()
    {
        try {
            $client = Factory::payment()->common();
            if ($this->config->appAuthToken) {
                $client->agent($this->config->appAuthToken);
            }
            $res = $client->create($this->config->subject, $this->config->tradeNo, $this->config->totalAmount, $this->config->userId);
            return $this->success($res);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function query()
    {
        try {
            $client = Factory::payment()->common();
            if ($this->config->appAuthToken) {
                $client->agent($this->config->appAuthToken);
            }
            $res = $client->query($this->config->tradeNo);
            return $this->success($res);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function refund()
    {
        try {
            $client = Factory::payment()->common();
            if ($this->config->appAuthToken) {
                $client->agent($this->config->appAuthToken);
            }
            if ($this->config->refundTradeNo) {
                $client->optional('out_request_no', $this->config->refundTradeNo);
            }
            $res = $client->refund($this->config->tradeNo, $this->config->totalAmount);
            return $this->success($res);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function refundQuery()
    {
        try {
            $res = Factory::payment()->common()->queryRefund($this->config->tradeNo, $this->config->refundTradeNo ?? $this->config->tradeNo);
            return $this->success($res);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @inheritDoc
     */
    function notify($data)
    {
        // TODO: Implement notify() method.
        if (!$this->verifySign($data)) {
            return $this->error('验签失败', -1);
        }
        if ($data['trade_status'] === 'TRADE_SUCCESS') {
            $merchantTradeNo = $data['out_trade_no'] ?? '';
            return $this->success(array_merge($data, compact('merchantTradeNo')));
        } else {
            return $this->error('交易未成功', -2);//实际不会触达，正常情况下只有TRADE_SUCCESS才会触发异步通知
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
    function sign($data)
    {
        throw new \Exception('不需要自己写签名方法');
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        return Factory::payment()->common()->verifyNotify($data);
    }
}
