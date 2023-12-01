<?php
declare(strict_types=1);

namespace Yijin\Pay\AbroadPayment;

use Yijin\Pay\Response;

class GKash extends Base
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
                'cid' => $this->config->gKashCID,
                'currency' => self::CURRENCY,
                'signature' => hash('sha512', strtoupper(implode(";", [
                    $this->config->gKashSignKey,
                    $this->config->gKashCID,
                    $this->config->tradeNo,
                    number_format($this->config->totalAmount, 2, '', ''),
                    self::CURRENCY
                ])))
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
        if (($data['status'] ?? '') === '88 - Transferred') {
            return $this->success(array_merge($data, [
                'merchantTradeNo' => $data['cartid'] ?? ''
            ]));
        }
        return $this->error('回调错误', -1);
    }

    /**
     * @inheritDoc
     */
    function notifySuccess(): string
    {
        return 'OK';
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
}
