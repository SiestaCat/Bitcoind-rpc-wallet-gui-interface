# Bitcoind rpc wallet gui interface
Basic bitcoin rpc client for wallets with symfony 6.

## Features

- Wallets list
- Create wallet
- Create address for wallet
- Generate QR for address
- Show BTC balance
- Change passphrase
- Send

## Requirements

- PHP 8
- Composer
- NPM

## Bitcoind rpc example

```
./bitcoind -prune=1024 -server -rest -rpcauth='user:77ba8bfe64e771ef76fc72a02ccf12bf$dad5b22c0503beb0945d723b9f267924131daac28653a0076e468533240b6193' -disablewallet=0
```

For this example, hashed password `77ba8bfe64e771ef76fc72a02ccf12bf$dad5b22c0503beb0945d723b9f267924131daac28653a0076e468533240b6193` is `user`

## Install
```
git clone git@github.com:SiestaCat/Bitcoind-rpc-wallet-gui-interface.git
cd Bitcoind-rpc-wallet-gui-interface
composer install
npm ci
composer dump-env prod
npm run build
```

### Configure env vars

Edit `.env.local.php` and fill the RCP hostname, port, username and password

#### Generate login password, select option [0] and paste it to `LOGIN_PASSWORD`

```
php bin/console security:hash-password
```

### Run on local server

```
php -S 0.0.0.0:8000 -t public
```
Open in browser https://localhost:8000/

## Screenshoots

![localhost_8000_login](https://github.com/SiestaCat/Bitcoind-rpc-wallet-gui-interface/assets/53893905/87ce3362-d873-4aed-9ab0-d8a9c32ab047)
![localhost_8000_](https://github.com/SiestaCat/Bitcoind-rpc-wallet-gui-interface/assets/53893905/0343ca2f-61cb-459f-9882-5698b883eb52)
![localhost_8000_wallet_form_create](https://github.com/SiestaCat/Bitcoind-rpc-wallet-gui-interface/assets/53893905/92aa5875-a088-4ba8-bf2b-4316930c9637)
![localhost_8000_wallet_show_mario](https://github.com/SiestaCat/Bitcoind-rpc-wallet-gui-interface/assets/53893905/3dc6cc36-f5ae-4236-aa26-cb8f47756a94)
