<?php

namespace App\Api\GS;

class WalletCreateForm
{
    public string $name;
    public string $passphrase;
    public bool $avoid_reuse = true;
}