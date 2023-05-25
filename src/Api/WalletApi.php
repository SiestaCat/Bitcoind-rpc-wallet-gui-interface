<?php

namespace App\Api;

use App\Api\GS\Fee;
use App\Api\GS\Wallet;
use App\RpcBitcoin\AuthException;
use App\RpcBitcoin\Client;
use App\RpcBitcoin\JsonDecodeException;
use Exception;

class WalletApi
{
    public function __construct(private Client $client, private Rawtransactions $rawtransactions)
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

        $getbalances = $this->getbalances($wallet_name);
        $wallet->balance_available = $getbalances->available;
        $wallet->balance_pending = $getbalances->pending;
        
        return $wallet;
    }

    public function getbalances(string $wallet_name):\stdClass
    {
        $balances = (object) ['available' => '0', 'pending' => '0'];
        $getbalances = $this->client->callWallet($wallet_name, 'getbalances');
        if(property_exists($getbalances, 'mine'))
        {
            $balances_mine = $getbalances->mine;
            if(property_exists($balances_mine, 'trusted')) $balances->available = $balances_mine->trusted;
            if(property_exists($balances_mine, 'untrusted_pending')) $balances->pending = $balances_mine->untrusted_pending;
        }
        return $balances;
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

    public function listtransactions(string $wallet_name):array
    {
        return $this->client->callWallet($wallet_name, 'listtransactions', [
            $wallet_name
        ]);
    }

    public function walletpassphrasechange(string $wallet_name, string $oldpassphrase, string $newpassphrase):void
    {
        $this->client->callWallet($wallet_name, 'walletpassphrasechange', [
            $oldpassphrase,
            $newpassphrase
        ]);
    }

    public function send(string $wallet_name, string $address, string $amount, string $fee_rate):bool
    {
        $result = $this->client->callWallet($wallet_name, 'send', [
            (
                (object)
                [
                    $address => $amount
                ]
            ),
            null,
            null,
            intval($fee_rate)
        ]);

        return $result->complete;
    }

    public function walletpassphrase(string $wallet_name, string $passphrase):void
    {
        $this->client->callWallet($wallet_name, 'walletpassphrase', [
            $passphrase
        ]);
    }

    /**
     * 
     * @return Fee[] 
     */
    public function getSendFees(string $wallet_name): array
    {
        /**
         * @var Fee[]
         */
        $fees = [];

        foreach
        (
            [
                2,4,6,12,24,48,144,504,1008
            ]
            as $blocks
        )
        {
            $estimatesmartfee = $this->client->callWallet($wallet_name, 'estimatesmartfee', [$blocks]);
            if(!property_exists($estimatesmartfee, 'feerate')) throw new \Exception('feerate property not exists in estimatesmartfee response. Response:' . json_encode($estimatesmartfee));
            $fee = new Fee;
            $fee->blocks = $blocks;
            $fee->btc_kvb = $estimatesmartfee->feerate;
            $fee->calculateSatVb();

            //Avoid repeated sat/vB values. Check if last fee have the same sat/vB value.
            $last_fee = (count($fees) > 0) ? $fees[count($fees) - 1] : null;
            if($last_fee !== null && $last_fee->btc_kvb == $fee->btc_kvb) continue;
            
            $fees[] = $fee;
        }

        return $fees;
    }

    public function getFee(string $wallet_name, string $feeRate, string $address, string $amount):\stdClass
    {
        $outputs = [];

        $outputs[] = (object) [
            $address => $amount
        ];

        return $this->client->callWallet($wallet_name, 'walletcreatefundedpsbt', [
            [],
            $outputs,
            0,
            (object) [
                'feeRate' => $feeRate,
                'subtractFeeFromOutputs' => [0]
            ]
        ]);
    }

    public function fundrawtransaction(string $wallet_name, string $hexstring):\stdClass
    {
        return $this->client->callWallet($wallet_name, 'decoderawtransaction', [
            $hexstring
        ]);
    }
}