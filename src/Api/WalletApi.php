<?php

namespace App\Api;

use App\Api\GS\Wallet;
use App\RpcBitcoin\Client;

class WalletApi
{
    public function __construct(private Client $client)
    {}

    public function list():array
    {
        $results = $this->client->call('listwallets');

        if(!is_array($results)) throw new \Exception('Unable to call listwallets');

        $wallets = [];

        foreach($results as $wallet_name)
        {
            $wallet = new Wallet;
            $wallet->name = $wallet_name;
            $wallets[] = $wallet;
            
        }

        return $wallets;
    }
}