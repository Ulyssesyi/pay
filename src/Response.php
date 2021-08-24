<?php

namespace Yijin\Pay;

trait Response
{
    public function success($data = ''): array
    {
        return [
            'result' => true,
            'data' => $data
        ];
    }

    public function error(string $errMsg, $msgNo): array
    {
        return [
            'result' => false,
            'errMsgNo' => $msgNo,
            'errMsg' => $errMsg
        ];
    }
}
