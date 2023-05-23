# Bitcoind rpc wallet gui interface
Basic bitcoin rpc client for wallets with symfony 6.

## Features

- Wallets list
- Create wallet
- Create address for wallet
- Show BTC balance
- Send (in progress..)

## Screenshoots

![localhost_8000_](https://github.com/SiestaCat/Bitcoind-rpc-wallet-gui-interface/assets/53893905/0343ca2f-61cb-459f-9882-5698b883eb52)
![localhost_8000_wallet_form_create](https://github.com/SiestaCat/Bitcoind-rpc-wallet-gui-interface/assets/53893905/92aa5875-a088-4ba8-bf2b-4316930c9637)
![localhost_8000_wallet_show_mario](https://github.com/SiestaCat/Bitcoind-rpc-wallet-gui-interface/assets/53893905/3dc6cc36-f5ae-4236-aa26-cb8f47756a94)


## Requirements

- PHP 8
- Composer
- NPM

## Install
```
git clone git@github.com:SiestaCat/Bitcoind-rpc-wallet-gui-interface.git
cd Bitcoind-rpc-wallet-gui-interface
composer install
npm ci
composer dump-env prod
npm run build
```

Generate login password, select option [0] and paste it to LOGIN_PASSWORD env var in file .env.local.php

```
php bin/console security:hash-password
```

Run on local server

```
php -S 0.0.0.0:8000 -t public
```