<?php

namespace App\RpcBitcoin\Clients;

use App\RpcBitcoin\Client;

final class WalletRpcClient extends Client
{

    public function __construct(string $rpc_username, string $rpc_password)
    {
        parent::__construct($rpc_username, $rpc_password);
    }

    public function listwallets():array|bool
    {

        $result = $this->call('listwallets');

        return is_array($result) ? $result : false;
    }
}