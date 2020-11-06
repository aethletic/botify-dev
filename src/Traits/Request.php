<?php

namespace Botify\Traits;

use Curl\Curl;

trait Request
{
    public function curl()
    {
        return new Curl();
    }

    public function request($method, $params = [], $isFile = false)
    {
        $curl = $this->curl();

        if ($isFile) {
            $curl->setHeader('Content-Type', 'multipart/form-data');
        } else {
            $curl->setHeader('Content-Type', 'application/json');
        }

        $curl->post($this->buildRequestUrl($method), $params);
        $curl->close();

        if ($curl->error) {
            echo 'Error code ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
            var_dump($this->buildRequestUrl($method), $params);
        } else {
            return collect($curl->response);
        }
    }

    public function buildRequestUrl($method)
    {
        return $this->apiUrl . $this->token . "/{$method}";
    }

    public function buildRequestFileUrl($fileUrl)
    {
        return $this->apiFileUrl . $this->token . "/{$fileUrl}";
    }

    private function buildRequestParams($params = [], $keyboard = false, $extra = [])
    {
        if ($keyboard) {
            $params['reply_markup'] = $keyboard;
        }

        $params['parse_mode'] = $this->config('telegram.parse_mode', 'html')->first();

        return array_merge($params, $extra);
    }
}
