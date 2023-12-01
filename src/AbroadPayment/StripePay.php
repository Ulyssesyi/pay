<?php
declare(strict_types=1);

namespace Yijin\Pay\AbroadPayment;

use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;
use Yijin\Pay\Response;

class StripePay extends Base
{
    use Response;

    /**
     * @inheritDoc
     */
    function terminalPay(): array
    {
        $stripe = new StripeClient($this->config->stripePrivateKey);

        try {
            $payment_intent_id = $this->config->paymentIntentId ?? '';
            $intent = $stripe->paymentIntents->capture($payment_intent_id);
            $status = 0;
            if ($intent->status === 'succeeded') {
                $status = 1;
            } else if ($intent->status === 'requires_payment_method') {
                $status = -1;
            }
            return $this->success(['trade_status' => $status]);
        } catch (ApiErrorException $e) {
            return $this->error($e->getMessage(), -1);
        }
    }

    /**
     * @inheritDoc
     */
    function barcodePay(): array
    {
        return $this->error('暂不支持条码支付', -1);
    }

    /**
     * @inheritDoc
     */
    function qrcodePay(): array
    {
        return $this->error('暂不支持二维码支付', -1);
    }

    /**
     * @inheritDoc
     */
    function webPay(): array
    {
        $stripe = new StripeClient($this->config->stripePrivateKey);
        try {
            $payment_method = $this->config->paymentMethod ?? null;
            $payment_method_type = $this->config->paymentMethodType ?? 'card';
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $this->config->totalAmount * 100,
                'currency' => 'sgd',
                'description' => $this->config->subject ?: 'RIPOS',
                'metadata' => [
                    'trade_no' => $this->config->tradeNo,
                ],
                'on_behalf_of' => $this->config->stripeAccount,
                'payment_method' => $payment_method,
                'payment_method_types' => [$payment_method_type],
                'payment_method_options' => $payment_method_type === 'card' ? [
                    'card' => [
                        'request_three_d_secure' => 'automatic',
                    ],
                ] : [],
            ]);
            return $this->success(array_merge($paymentIntent->toArray(), [
                'jsApiParameters' => [
                    'clientSecret' => $paymentIntent->client_secret,
                    'publicSecret' => $this->config->stripePublicKey,
                ],
            ]));
        } catch (ApiErrorException $e) {
            return $this->error($e->getMessage(), -1);
        }
    }

    /**
     * @inheritDoc
     */
    function query(): array
    {
        $stripe = new StripeClient($this->config->stripePrivateKey);

        try {
            $intent = $stripe->paymentIntents->retrieve($this->config->tradeNo);
            switch ($intent->status) {
                case 'succeeded':
                    $trade_status = 1;
                    break;
                case 'requires_payment_method':
                    $trade_status = -1;
                    break;
                default:
                    $trade_status = 0;
            }
            return $this->success(array_merge($intent->toArray(), compact('trade_status')));
        } catch (ApiErrorException $e) {
            return $this->error($e->getMessage(), -1);
        }
    }

    /**
     * @inheritDoc
     */
    function refund(): array
    {
        return $this->error('暂不支持退款', -1);
    }

    /**
     * @inheritDoc
     */
    function refundQuery(): array
    {
        return $this->error('暂不支持退款查询', -1);
    }

    /**
     * @inheritDoc
     */
    function notify($data): array
    {
        list($sign, $requestData) = $data;

        try {
            $event = Webhook::constructEvent(
                $requestData, $sign, $this->config->stripeEndKey
            );
        } catch (SignatureVerificationException $e) {
            return $this->error($e->getMessage(), -1);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                return $this->success(
                    array_merge($event->data->toArray(), [
                        'merchantTradeNo' => $event->data->offsetGet('object')->metadata->offsetGet('trade_no'),
                    ])
                );
            default:
                return $this->error('未知事件类型', -2);
        }
    }

    /**
     * @inheritDoc
     */
    function notifySuccess(): string
    {
        return 'success';
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
