<?php
declare(strict_types=1);

namespace Yijin\Pay\AbroadPayment;

use Yijin\Pay\Response;

class MayBank extends Base
{
    use Response;

    const CURRENCY = 'MYR';

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
        return $this->error('暂不支持', -1);
    }

    /**
     * @inheritDoc
     */
    function webPay(): array
    {
        return $this->success([
            'jsApiParameters' => [
                'encryptedString' => $this->getEncryptString(),
                'actionUrl' => $this->config->isSandbox ? 'https://m2upayuat.maybank2u.com.my/testM2uPayment' : 'https://www.maybank2u.com.my/mbb/m2u/m9006_enc/m2uMerchantLogin.do'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    function query(): array
    {
        return $this->error('暂不支持', -1);
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
        if (($data['StatusCode'] ?? '') === '00') {
            return $this->success(array_merge($data, [
                'merchantTradeNo' => $data['AcctId'] ?? ''
            ]));
        }
        return $this->error('回调错误', -1);
    }

    /**
     * @inheritDoc
     */
    function notifySuccess(array $params = []): string
    {
        return json_encode([
            'Msg' => [
                'RefId' => $params['RefId'],
                'PmtType' => $params['PmtType'],
                'StatusCode' => 0
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    function sign($data): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    function verifySign(array $data): bool
    {
        return true;
    }

    private function getEncryptString(): string
    {
        $salt = "Maybank2u simple encryption";
        $secretKey = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        if (!$secretKey) {
            return '';
        }

        $string = 'Login$' . $this->config->mayBankMerchantCode . '$1$' . $this->config->totalAmount . '$$$1$' . $this->config->tradeNo . '$' . $this->config->notifyUrl;

        for ($i = 0; $i < 2; $i++) {
            $string = rtrim(openssl_encrypt($salt . $string, 'aes-128-ecb', $secretKey));
        }
        return urlencode($string);
    }
}
