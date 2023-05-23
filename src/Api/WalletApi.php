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
        $wallets = [];

        //Loaded wallets

        $listwallets = $this->client->call('listwallets');

        foreach($listwallets as $wallet_name)
        {
            $wallets[$wallet_name] = $this->get($wallet_name);
        }

        //Not loaded wallets

        $listwalletdir = $this->client->call('listwalletdir');

        if(property_exists($listwalletdir, 'wallets'))
        {
            foreach($listwalletdir->wallets as $wallet_object)
            {
                if(array_key_exists($wallet_object->name, $wallets)) continue;
                $wallet = new Wallet;
                $wallet->is_loaded = false;
                $wallet->name = $wallet_object->name;
                $wallets[$wallet_object->name] = $wallet;
            }
        }

        return array_values($wallets);
    }

    public function get(string $wallet_name):Wallet
    {
        $wallet = new Wallet;
        $wallet->name = $wallet_name;
        $wallet->is_loaded = true;
        $wallet->addresses = $this->getaddressesbylabel($wallet_name);

        $getbalances = $this->client->callWallet($wallet_name, 'getbalances');
        if(property_exists($getbalances, 'mine'))
        {
            $balances_mine = $getbalances->mine;
            if(property_exists($balances_mine, 'trusted')) $wallet->balance_available = $balances_mine->trusted;
            if(property_exists($balances_mine, 'untrusted_pending')) $wallet->balance_pending = $balances_mine->untrusted_pending;
        }
        return $wallet;
    }

    /**
     * Create wallet
     * https://developer.bitcoin.org/reference/rpc/createwallet.html
     * @param string $name 
     * @param string $passphrase 
     * @param bool $avoid_reuse 
     * @return void 
     * @throws AuthException 
     * @throws JsonDecodeException 
     * @throws Exception 
     */
    public function create(string $name, string $passphrase, bool $avoid_reuse):void
    {
        $this->client->call('createwallet', [
            $name,
            false, //disable_private_keys false by default
            false, //blank wallet
            $passphrase,
            $avoid_reuse,
            false, //descriptors false by default
            true //load_on_startup
        ]);
    }

    public function load(string $name):void
    {
        $this->client->call('loadwallet', [
            $name
        ]);
    }

    public function getnewaddress(string $wallet_name, string $address_type):string
    {
        return $this->client->callWallet($wallet_name, 'getnewaddress', [
            $wallet_name,
            $address_type
        ]);
    }

    public function getaddressesbylabel(string $wallet_name):array
    {
        try
        {
            $addresses = $this->client->callWallet($wallet_name, 'getaddressesbylabel', [
                $wallet_name
            ]);
            return array_keys((array) $addresses);
        }
        catch(\Exception $e)
        {
            if(!str_contains($e->getMessage(), 'No addresses with label')) throw $e;
        }
        
        return [];
        
    }
}