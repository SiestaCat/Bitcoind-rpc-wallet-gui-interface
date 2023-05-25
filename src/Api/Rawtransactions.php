<?php

namespace App\Api;

use App\RpcBitcoin\Client;

class Rawtransactions
{
    public function __construct(private Client $client)
    {}

    

    public function combinerawtransaction(array $hexstrings):string
    {
        return $this->client->call('combinerawtransaction', [
            $hexstrings
        ]);
    }
}