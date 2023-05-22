<?php

namespace App\RpcBitcoin;

class Client extends HttpClientcaller
{

    public function __construct(string $rpc_username, string $rpc_password)
    {
        parent::__construct($rpc_username, $rpc_password);
    }

    public function call(string $method, array $params = []):mixed
    {
        return $this->make_curl_call($method, $params);
    }
}