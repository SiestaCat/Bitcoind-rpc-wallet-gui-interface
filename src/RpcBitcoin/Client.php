<?php

namespace App\RpcBitcoin;

class Client
{

    const SOCK_TIMEOUT = 2;

    const JSON_RPC = '1.0';

    public function __construct(private string $rpc_hostname, private int $rpc_port, private string $rpc_username, private string $rpc_password, private bool $rpc_is_https)
    {}

    public function call(string $method, array $params = []):mixed
    {
        return $this->make_curl_call($method, $params);
    }

    public function isUp():bool
    {
        $connection = @fsockopen($this->rpc_hostname, $this->rpc_port, $null, $null, self::SOCK_TIMEOUT);

        if (is_resource($connection))
        {
            fclose($connection);
            return true;
        }
        else
        {
            return false;
        }
    }


    /**
     * 
     * @param string $method 
     * @param array $params 
     * @return string|array|bool|null
     */
    private function make_curl_call(string $method, array $params = []):mixed
    {
        $url = sprintf('http%s://%s:%s/', ($this->rpc_is_https ? 's' : ''), $this->rpc_hostname, $this->rpc_port);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/plain',
            'Authorization: Basic ' . base64_encode($this->rpc_username . ':' . $this->rpc_password)
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->generateJson($method, $params)));

        $response = curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if($httpcode === 401) throw new AuthException('Invalid login');

        $json_decoded = json_decode($response);

        if(!is_object($json_decoded)) throw new JsonDecodeException('Unable to decode JSON result. Curl response: ' . $response);

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