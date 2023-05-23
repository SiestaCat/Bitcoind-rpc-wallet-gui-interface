<?php

namespace App\Api;

use App\Api\GS\Wallet;
use App\RpcBitcoin\AuthException;
use App\RpcBitcoin\Client;
use App\RpcBitcoin\JsonDecodeException;
use Exception;

class WalletApi
{
    public function __construct(private Client $client)
    {}

    /**
     * 
     * @return Wallet[] 
     * @throws AuthException 
     * @throws JsonDecodeException 
     * @throws Exception 
     */
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

    public function create(string $name, string $passphrase, bool $avoid_reuse):void
    {
        $this->client->call('createwallet', [
            $name,
            false,
            true, //blank wallet
            $passphrase,
            $avoid_reuse
        ]);
    }
}