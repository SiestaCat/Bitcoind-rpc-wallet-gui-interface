<?php

namespace App\RpcBitcoin;

class HttpClientcaller
{

    const JSON_RPC = '1.0';


    public function __construct(private string $rpc_username, private string $rpc_password)
    {
    }

    /**
     * 
     * @param string $method 
     * @param array $params 
     * @return string|array|bool|null
     */
    protected function make_curl_call(string $method, array $params = []):mixed
    {
        $ch = curl_init("http://127.0.0.1:8332/");

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/plain',
            'Authorization: Basic ' . base64_encode($this->rpc_username . ':' . $this->rpc_password)
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->generateJson($method, $params)));

        $response = curl_exec($ch);

        curl_close($ch);

        $json_decoded = json_decode($response);

        if(!is_object($json_decoded)) throw new \Exception('Unable to decode JSON result. Result: ' . $response);

        return $json_decoded->result;
    }

    private function generateJson(string $method, array $params = []):array
    {
        $json = 
        [
            'jsonrpc' => self::JSON_RPC,
            'id' => '$curltest',
            'method' => $method,
            'params' => $params
        ];

        return $json;
    }
}