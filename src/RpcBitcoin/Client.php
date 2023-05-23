<?php

namespace App\RpcBitcoin;

class Client
{

    const SOCK_TIMEOUT = 2;

    const JSON_RPC = '1.0';

    public function __construct(private string $rpc_hostname, private int $rpc_port, private string $rpc_username, private string $rpc_password, private bool $rpc_is_https = false)
    {
        
    }

    public function listwallets():array|bool
    {
        $result = $this->call('listwallets');

        return is_array($result) ? $result : false;
    }

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