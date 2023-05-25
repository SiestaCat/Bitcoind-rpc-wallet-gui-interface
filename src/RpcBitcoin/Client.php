<?php

namespace App\RpcBitcoin;

class Client
{

    const SOCK_TIMEOUT = 2;

    const JSON_RPC = '1.0';

    private ?string $last_response = null;

    public function __construct(private string $rpc_hostname, private int $rpc_port, private string $rpc_username, private string $rpc_password, private bool $rpc_is_https)
    {}

    public function call(string $method, array $params = []):mixed
    {
        return $this->make_curl_call($method, $params);
    }

    public function callWallet(string $wallet_name, string $method, array $params = []):mixed
    {
        return $this->make_curl_call($method, $params, ('wallet/' . $wallet_name));
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

    public function getLastResponse():?string
    {
        return $this->last_response;
    }


    /**
     * 
     * @param string $method 
     * @param array $params 
     * @return string|array|bool|null
     */
    private function make_curl_call(string $method, array $params = [], ?string $sufix = null):mixed
    {
        $url = sprintf('http%s://%s:%s/', ($this->rpc_is_https ? 's' : ''), $this->rpc_hostname, $this->rpc_port) . $sufix;

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

        if(is_string($response) || $response === null) $this->last_response = $response;

        if($httpcode === 401) throw new AuthException('Invalid login');

        $json_decoded = json_decode($response);

        //Convert int and float to string

        $json_decoded = $json_decoded === false ? false : (json_decode(preg_replace('/\: *([0-9]+\.?[0-9e+\-]*)/', ':"\\1"', $response)));

        if(!is_object($json_decoded)) throw new JsonDecodeException('Unable to decode JSON result. Curl response: ' . $response);

        if(property_exists($json_decoded, 'error') && $json_decoded->error !== null) throw new \Exception(json_encode($json_decoded->error));

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