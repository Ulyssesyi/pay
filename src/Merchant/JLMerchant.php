<?php

namespace Yijin\Pay\Merchant;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Yijin\Pay\Response;

class JLMerchant
{
    use Response;
    public bool $isSandbox = true;
    public string $agentId;
    public string $merchantNo;
    public string $privateKey;
    public function clientAddQrDevice(): array
    {
        $params = [
            'agentId' => $this->agentId,
            'msgTranCode' => 'MER009',
            'source' => '9',
            'merchNo' => $this->merchantNo,
            'signMethod' => '02',
            'areaCode' => '440305',
            "detAddress" => "广东省深圳南山区科技生态园22栋",
            'signData' => 'agentId,source,merchNo,signMethod',
        ];
        try {
            $res = $this->execRequest($params, '/access/merch/clientAddQrDevice');
            if (isset($res['ret_code']) && $res['ret_code'] == '00') {
                return $this->success($res['term_no']);
            }
            return $this->error($res['ret_msg'] ?? '', $res['ret_code'] ?? '');
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    function sign($data): string
    {
        $signData = explode(',', $data['signData']);
        $signStr = '';
        foreach ($signData as $key) {
            $signStr .= $data[$key];
        }
        $privateKey = openssl_pkey_get_private("-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap($this->privateKey, 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----");
        openssl_sign($signStr, $sign, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }
    /**
     * @throws GuzzleException
     */
    private function execRequest(array $params, string $url)
    {
        $params['signData'] = $this->sign($params);

        $client = new Client([
            'base_uri' => $this->isSandbox ? 'https://openapi-uat.jlpay.com' : 'https://openapi.jlpay.com',
            'timeout' => $this->config->requestTimeout ?? 10,
        ]);
        $response = $client->post($url, [
            'json' => $params
        ]);
        $responseData = $response->getBody()->getContents();
        return json_decode($responseData, true);
    }
}
